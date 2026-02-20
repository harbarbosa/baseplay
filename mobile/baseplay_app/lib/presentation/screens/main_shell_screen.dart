import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../state/providers.dart';

class MainShellScreen extends ConsumerWidget {
  final Widget child;
  final String location;

  const MainShellScreen({
    super.key,
    required this.child,
    required this.location,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final permissions = ref.watch(authUserProvider)?.permissions.toSet() ?? <String>{};
    final destinations = _destinationsForPermissions(permissions);
    final currentIndex = _indexFromLocation(location, destinations);

    return Scaffold(
      body: child,
      bottomNavigationBar: NavigationBar(
        selectedIndex: currentIndex,
        onDestinationSelected: (index) {
          context.go(destinations[index].route);
        },
        destinations: destinations
            .map(
              (item) => NavigationDestination(
                icon: Icon(item.iconOutlined),
                selectedIcon: Icon(item.iconFilled),
                label: item.label,
              ),
            )
            .toList(growable: false),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
      floatingActionButton: currentIndex == 3
          || (currentIndex >= 0 && destinations[currentIndex].route == '/home/profile')
          ? null
          : FloatingActionButton.small(
              tooltip: 'Sair',
              onPressed: () async {
                await ref.read(authControllerProvider.notifier).logout();
                if (context.mounted) {
                  context.go('/login');
                }
              },
              child: const Icon(Icons.logout),
            ),
    );
  }

  int _indexFromLocation(String value, List<_NavItem> items) {
    final idx = items.indexWhere((item) => value.startsWith(item.route));
    if (idx >= 0) {
      return idx;
    }
    return 0;
  }

  List<_NavItem> _destinationsForPermissions(Set<String> permissions) {
    final has = (String permission) => permissions.contains(permission);
    final items = <_NavItem>[];

    if (has('events.view')) {
      items.add(
        const _NavItem(
          route: '/home/agenda',
          label: 'Agenda',
          iconOutlined: Icons.calendar_month_outlined,
          iconFilled: Icons.calendar_month,
        ),
      );
    }

    if (has('notices.view')) {
      items.add(
        const _NavItem(
          route: '/home/notices',
          label: 'Avisos',
          iconOutlined: Icons.campaign_outlined,
          iconFilled: Icons.campaign,
        ),
      );
    }

    if (has('athletes.view')) {
      items.add(
        const _NavItem(
          route: '/home/athletes',
          label: 'Atletas',
          iconOutlined: Icons.groups_2_outlined,
          iconFilled: Icons.groups_2,
        ),
      );
    }

    if (has('dashboard.view') || items.isEmpty) {
      items.add(
        const _NavItem(
          route: '/home/profile',
          label: 'Painel',
          iconOutlined: Icons.dashboard_outlined,
          iconFilled: Icons.dashboard,
        ),
      );
    }

    return items;
  }
}

class _NavItem {
  final String route;
  final String label;
  final IconData iconOutlined;
  final IconData iconFilled;

  const _NavItem({
    required this.route,
    required this.label,
    required this.iconOutlined,
    required this.iconFilled,
  });
}
