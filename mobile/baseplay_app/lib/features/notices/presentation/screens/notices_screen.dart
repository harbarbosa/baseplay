import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../domain/models/notice.dart';
import '../state/notices_providers.dart';
import '../../../../presentation/widgets/team_selector_action.dart';
import '../../../../core/auth/permissions.dart';
import '../../domain/models/notice_reply.dart';
import '../../../../presentation/state/providers.dart';

class NoticesScreen extends ConsumerWidget {
  const NoticesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final noticesAsync = ref.watch(noticesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Avisos'),
        actions: const [TeamSelectorAction()],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(noticesProvider),
        child: noticesAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(24),
            children: [
              Text(error.toString(), textAlign: TextAlign.center),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () => ref.invalidate(noticesProvider),
                child: const Text('Tentar novamente'),
              ),
            ],
          ),
          data: (notices) {
            if (notices.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 80),
                  Icon(
                    Icons.campaign_outlined,
                    size: 40,
                    color: Colors.black45,
                  ),
                  SizedBox(height: 10),
                  Center(child: Text('Sem avisos no momento.')),
                ],
              );
            }

            return ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
              itemCount: notices.length,
              separatorBuilder: (_, index) => const SizedBox(height: 10),
              itemBuilder: (context, index) {
                final notice = notices[index];
                return Card(
                  child: ListTile(
                    onTap: () => context.push('/home/notices/${notice.id}'),
                    title: Text(notice.title),
                    subtitle: Text(
                      notice.message,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    trailing: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        _PriorityBadge(priority: notice.priority),
                        const SizedBox(height: 6),
                        Text(
                          notice.isRead ? 'Lido' : 'Não lido',
                          style: TextStyle(
                            fontSize: 11,
                            color: notice.isRead ? Colors.green : Colors.orange,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}

class NoticeDetailScreen extends ConsumerWidget {
  final int noticeId;

  const NoticeDetailScreen({super.key, required this.noticeId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final noticeAsync = ref.watch(noticeDetailProvider(noticeId));
    final repliesAsync = ref.watch(noticeRepliesProvider(noticeId));
    final canReply = ref
            .watch(authUserProvider)
            ?.hasPermission(Permissions.noticesReply) ??
        false;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Aviso'),
        actions: const [TeamSelectorAction()],
      ),
      body: noticeAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
        data: (notice) => _NoticeDetailContent(
          notice: notice,
          repliesAsync: repliesAsync,
          canReply: canReply,
        ),
      ),
      bottomNavigationBar: noticeAsync.maybeWhen(
        data: (notice) {
          if (notice.isRead) {
            return const SizedBox.shrink();
          }
          return SafeArea(
            minimum: const EdgeInsets.all(16),
            child: ElevatedButton(
              onPressed: () async {
                try {
                  await ref
                      .read(noticeActionControllerProvider)
                      .markAsRead(notice.id);
                  if (!context.mounted) return;
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Aviso marcado como lido.')),
                  );
                } catch (error) {
                  if (!context.mounted) return;
                  ScaffoldMessenger.of(
                    context,
                  ).showSnackBar(SnackBar(content: Text(error.toString())));
                }
              },
              child: const Text('Marcar como lido'),
            ),
          );
        },
        orElse: () => const SizedBox.shrink(),
      ),
    );
  }
}

class _NoticeDetailContent extends StatelessWidget {
  final Notice notice;
  final AsyncValue<List<NoticeReply>> repliesAsync;
  final bool canReply;

  const _NoticeDetailContent({
    required this.notice,
    required this.repliesAsync,
    required this.canReply,
  });

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                notice.title,
                style: Theme.of(context).textTheme.headlineSmall,
              ),
            ),
            _PriorityBadge(priority: notice.priority),
          ],
        ),
        const SizedBox(height: 12),
        Text(
          notice.publishAt != null
              ? 'Publicado em ${_formatDateTime(notice.publishAt!)}'
              : 'Sem data de publicação',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 16),
        Text(notice.message, style: Theme.of(context).textTheme.bodyLarge),
        const SizedBox(height: 20),
        Text('Respostas', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        repliesAsync.when(
          loading: () => const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: LinearProgressIndicator(minHeight: 2),
          ),
          error: (error, _) => Text(error.toString()),
          data: (replies) {
            if (replies.isEmpty) {
              return const Text('Sem respostas ainda.');
            }
            return Column(
              children:
                  replies.map((reply) => _ReplyTile(reply: reply)).toList(),
            );
          },
        ),
        if (canReply) ...[
          const SizedBox(height: 16),
          _ReplyComposer(noticeId: notice.id),
        ],
      ],
    );
  }

  String _formatDateTime(DateTime dateTime) {
    final dd = dateTime.day.toString().padLeft(2, '0');
    final mm = dateTime.month.toString().padLeft(2, '0');
    final yyyy = dateTime.year.toString();
    final hh = dateTime.hour.toString().padLeft(2, '0');
    final min = dateTime.minute.toString().padLeft(2, '0');
    return '$dd/$mm/$yyyy $hh:$min';
  }
}

class _ReplyTile extends StatelessWidget {
  final NoticeReply reply;

  const _ReplyTile({required this.reply});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(reply.authorName),
        subtitle: Text(reply.message),
        trailing: Text(
          reply.createdAt != null ? _formatDateTime(reply.createdAt!) : '--',
          style: Theme.of(context).textTheme.labelSmall,
        ),
      ),
    );
  }

  String _formatDateTime(DateTime dateTime) {
    final dd = dateTime.day.toString().padLeft(2, '0');
    final mm = dateTime.month.toString().padLeft(2, '0');
    final yyyy = dateTime.year.toString();
    final hh = dateTime.hour.toString().padLeft(2, '0');
    final min = dateTime.minute.toString().padLeft(2, '0');
    return '$dd/$mm/$yyyy $hh:$min';
  }
}

class _ReplyComposer extends ConsumerStatefulWidget {
  final int noticeId;

  const _ReplyComposer({required this.noticeId});

  @override
  ConsumerState<_ReplyComposer> createState() => _ReplyComposerState();
}

class _ReplyComposerState extends ConsumerState<_ReplyComposer> {
  final _controller = TextEditingController();
  bool _sending = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: _controller,
          minLines: 2,
          maxLines: 4,
          decoration: const InputDecoration(labelText: 'Sua resposta'),
        ),
        const SizedBox(height: 8),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _sending ? null : _send,
            icon: const Icon(Icons.send),
            label: _sending
                ? const Text('Enviando...')
                : const Text('Enviar resposta'),
          ),
        ),
      ],
    );
  }

  Future<void> _send() async {
    final text = _controller.text.trim();
    if (text.isEmpty) {
      return;
    }
    setState(() => _sending = true);
    try {
      await ref
          .read(noticeReplyControllerProvider)
          .send(widget.noticeId, text);
      _controller.clear();
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error.toString().replaceAll('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() => _sending = false);
      }
    }
  }
}

class _PriorityBadge extends StatelessWidget {
  final String priority;

  const _PriorityBadge({required this.priority});

  @override
  Widget build(BuildContext context) {
    Color background;
    Color foreground;
    String label;

    switch (priority) {
      case 'urgent':
        background = Colors.red.shade100;
        foreground = Colors.red.shade900;
        label = 'Urgente';
        break;
      case 'important':
        background = Colors.orange.shade100;
        foreground = Colors.orange.shade900;
        label = 'Importante';
        break;
      default:
        background = Colors.blueGrey.shade100;
        foreground = Colors.blueGrey.shade900;
        label = 'Normal';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w700,
          color: foreground,
        ),
      ),
    );
  }
}

