import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';

import '../../data/document_repository.dart';
import '../../domain/models/document_type_model.dart';
import '../state/documents_providers.dart';

class DocumentUploadScreen extends ConsumerStatefulWidget {
  final int athleteId;

  const DocumentUploadScreen({super.key, required this.athleteId});

  @override
  ConsumerState<DocumentUploadScreen> createState() =>
      _DocumentUploadScreenState();
}

class _DocumentUploadScreenState extends ConsumerState<DocumentUploadScreen> {
  final _formKey = GlobalKey<FormState>();
  final _notesController = TextEditingController();
  DateTime? _issuedAt;
  DateTime? _expiresAt;
  DocumentTypeModel? _selectedType;
  String? _filePath;
  Uint8List? _fileBytes;
  String? _fileName;
  bool _sending = false;

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final typesAsync = ref.watch(documentTypesProvider);
    final progress = ref.watch(documentsUploadProgressProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Upload de documento')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                typesAsync.when(
                  loading: () => const LinearProgressIndicator(),
                  error: (error, stackTrace) => Text(error.toString()),
                  data: (types) => DropdownButtonFormField<DocumentTypeModel>(
                    key: ValueKey(_selectedType?.id ?? 0),
                    initialValue: _selectedType,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Tipo de documento',
                    ),
                    items: types
                        .map(
                          (type) => DropdownMenuItem(
                            value: type,
                            child: Text(type.name),
                          ),
                        )
                        .toList(),
                    onChanged: _sending
                        ? null
                        : (value) => setState(() => _selectedType = value),
                    validator: (value) =>
                        value == null ? 'Selecione o tipo.' : null,
                  ),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _sending ? null : _pickFile,
                        icon: const Icon(Icons.attach_file),
                        label: const Text('Arquivo'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _sending ? null : _pickFromCamera,
                        icon: const Icon(Icons.camera_alt_outlined),
                        label: const Text('Camera'),
                      ),
                    ),
                  ],
                ),
                if (_fileName != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text('Selecionado: $_fileName'),
                  ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _sending
                            ? null
                            : () async {
                                final date = await _pickDate(_issuedAt);
                                if (date != null) {
                                  setState(() => _issuedAt = date);
                                }
                              },
                        child: Text('Emissao: ${_fmtDate(_issuedAt)}'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _sending
                            ? null
                            : () async {
                                final date = await _pickDate(_expiresAt);
                                if (date != null) {
                                  setState(() => _expiresAt = date);
                                }
                              },
                        child: Text('Vencimento: ${_fmtDate(_expiresAt)}'),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _notesController,
                  minLines: 2,
                  maxLines: 3,
                  decoration: const InputDecoration(labelText: 'Observacao'),
                ),
                const SizedBox(height: 16),
                if (_sending)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Enviando...'),
                      const SizedBox(height: 6),
                      LinearProgressIndicator(
                        value: progress > 0 ? progress : null,
                      ),
                      const SizedBox(height: 14),
                    ],
                  ),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _sending ? null : _submit,
                    icon: const Icon(Icons.cloud_upload_outlined),
                    label: const Text('Enviar documento'),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
      withData: kIsWeb,
    );

    final files = result?.files;
    if (files == null || files.isEmpty) {
      return;
    }
    final file = files.first;

    setState(() {
      _fileName = file.name;
      _filePath = file.path;
      _fileBytes = file.bytes;
    });
  }

  Future<void> _pickFromCamera() async {
    final picker = ImagePicker();
    final captured = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 90,
    );
    if (captured == null) {
      return;
    }
    Uint8List? bytes;
    String? path;
    if (kIsWeb) {
      bytes = await captured.readAsBytes();
    } else {
      path = captured.path;
    }

    setState(() {
      _fileName = captured.name;
      _fileBytes = bytes;
      _filePath = path;
    });
  }

  Future<DateTime?> _pickDate(DateTime? initial) async {
    final now = DateTime.now();
    return showDatePicker(
      context: context,
      initialDate: initial ?? now,
      firstDate: DateTime(2010),
      lastDate: DateTime(2100),
    );
  }

  Future<void> _submit() async {
    if (_formKey.currentState?.validate() != true) {
      return;
    }
    if (_fileName == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Selecione um arquivo.')));
      return;
    }

    if (_selectedType?.requiresExpiration == true && _expiresAt == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Este tipo exige data de vencimento.')),
      );
      return;
    }

    if (_issuedAt != null &&
        _expiresAt != null &&
        _expiresAt!.isBefore(_issuedAt!)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Vencimento deve ser maior ou igual a emissao.'),
        ),
      );
      return;
    }

    setState(() => _sending = true);

    try {
      await ref
          .read(documentUploadControllerProvider)
          .upload(
            DocumentUploadRequest(
              athleteId: widget.athleteId,
              documentTypeId: _selectedType!.id,
              fileName: _fileName!,
              filePath: _filePath,
              bytes: _fileBytes,
              notes: _notesController.text,
              issuedAt: _issuedAt,
              expiresAt: _expiresAt,
            ),
          );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Documento enviado com sucesso.')),
      );
      Navigator.of(context).pop();
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

  String _fmtDate(DateTime? date) {
    if (date == null) {
      return '--';
    }
    final d = date.day.toString().padLeft(2, '0');
    final m = date.month.toString().padLeft(2, '0');
    final y = date.year.toString();
    return '$d/$m/$y';
  }
}
