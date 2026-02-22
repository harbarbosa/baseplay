import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import '../../athletes/data/athlete_repository.dart';
import '../../athletes/domain/models/athlete_model.dart';
import '../domain/models/document_model.dart';
import '../domain/models/document_type_model.dart';
import '../domain/models/documents_overview_model.dart';

class DocumentUploadRequest {
  final int athleteId;
  final int documentTypeId;
  final String? notes;
  final DateTime? issuedAt;
  final DateTime? expiresAt;
  final String fileName;
  final String? filePath;
  final List<int>? bytes;

  const DocumentUploadRequest({
    required this.athleteId,
    required this.documentTypeId,
    required this.fileName,
    this.filePath,
    this.bytes,
    this.notes,
    this.issuedAt,
    this.expiresAt,
  });
}

class DocumentRepository {
  final ApiClient _api;
  final AthleteRepository _athletes;

  DocumentRepository(this._api, this._athletes);

  Future<List<DocumentModel>> listByAthlete(int athleteId) async {
    try {
      final response = await _api.dio.get(
        Endpoints.documents,
        queryParameters: {'athlete_id': athleteId, 'per_page': 100},
      );

      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];

      return rawItems
          .map((item) => DocumentModel.fromJson(ApiResponseParser.asMap(item)))
          .toList();
    } on DioException catch (e) {
      throw _mapException(e, fallback: 'Não foi possível carregar documentos.');
    }
  }

  Future<List<DocumentTypeModel>> listTypes() async {
    try {
      final response = await _api.dio.get(Endpoints.documentTypes);
      final payload = ApiResponseParser.extractData(response.data);
      final rawItems = (payload as List?) ?? const [];
      return rawItems
          .map(
            (item) => DocumentTypeModel.fromJson(ApiResponseParser.asMap(item)),
          )
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar tipos de documento.',
      );
    }
  }

  Future<void> upload(
    DocumentUploadRequest request, {
    void Function(int sent, int total)? onProgress,
  }) async {
    try {
      MultipartFile filePart;
      if (kIsWeb) {
        final fileBytes = request.bytes;
        if (fileBytes == null) {
          throw const ApiException('Arquivo inválido para upload.');
        }
        filePart = MultipartFile.fromBytes(
          fileBytes,
          filename: request.fileName,
        );
      } else {
        final path = request.filePath;
        if (path == null || path.isEmpty || !File(path).existsSync()) {
          throw const ApiException('Arquivo inválido para upload.');
        }
        filePart = await MultipartFile.fromFile(
          path,
          filename: request.fileName,
        );
      }

      final form = FormData.fromMap({
        'athlete_id': request.athleteId.toString(),
        'document_type_id': request.documentTypeId.toString(),
        'status': 'active',
        if (request.notes != null && request.notes!.trim().isNotEmpty)
          'notes': request.notes!.trim(),
        if (request.issuedAt != null)
          'issued_at': _formatDate(request.issuedAt!),
        if (request.expiresAt != null)
          'expires_at': _formatDate(request.expiresAt!),
        'document_file': filePart,
      });

      await _api.dio.post(
        Endpoints.documents,
        data: form,
        onSendProgress: onProgress,
      );
    } on DioException catch (e) {
      throw _mapException(e, fallback: 'Não foi possível enviar documento.');
    }
  }

  Future<DocumentsOverviewModel> getOverview({int? categoryId}) async {
    try {
      final athletes = await _allAthletes(categoryId: categoryId);
      if (athletes.isEmpty) {
        return const DocumentsOverviewModel(
          expired: 0,
          expiring: 0,
          missing: 0,
        );
      }

      var expired = 0;
      var expiring = 0;
      var missing = 0;

      await Future.wait(
        athletes.map((athlete) async {
          final docs = await listByAthlete(athlete.id);
          if (docs.isEmpty) {
            missing++;
            return;
          }

          final hasExpired = docs.any((d) => d.isExpired);
          final hasExpiring = docs.any((d) => d.isExpiringSoon);

          if (hasExpired) {
            expired++;
          } else if (hasExpiring) {
            expiring++;
          }
        }),
      );

      return DocumentsOverviewModel(
        expired: expired,
        expiring: expiring,
        missing: missing,
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar visão geral de documentos.',
      );
    }
  }

  Future<List<AthleteModel>> _allAthletes({int? categoryId}) async {
    final all = <AthleteModel>[];
    var page = 1;

    while (true) {
      final response = await _athletes.listAthletes(
        categoryId: categoryId,
        page: page,
        perPage: 50,
      );
      all.addAll(response.items);
      if (page >= response.pageCount) {
        break;
      }
      page++;
    }

    return all;
  }

  String _formatDate(DateTime value) {
    final y = value.year.toString().padLeft(4, '0');
    final m = value.month.toString().padLeft(2, '0');
    final d = value.day.toString().padLeft(2, '0');
    return '$y-$m-$d';
  }

  ApiException _mapException(DioException error, {required String fallback}) {
    final statusCode = error.response?.statusCode;
    final message = ApiResponseParser.extractMessage(
      error.response?.data,
      fallback: fallback,
    );

    return ApiException(
      message,
      statusCode: statusCode,
      isUnauthorized: statusCode == 401,
    );
  }
}


