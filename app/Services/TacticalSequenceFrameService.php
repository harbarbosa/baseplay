<?php

namespace App\Services;

use App\Models\TacticalSequenceFrameModel;
use App\Models\TacticalSequenceModel;
use CodeIgniter\Database\BaseConnection;

class TacticalSequenceFrameService
{
    protected TacticalSequenceFrameModel $frames;
    protected TacticalSequenceModel $sequences;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->frames = new TacticalSequenceFrameModel();
        $this->sequences = new TacticalSequenceModel();
        $this->db = db_connect();
    }

    public function listBySequence(int $sequenceId): array
    {
        return $this->frames
            ->where('tactical_sequence_id', $sequenceId)
            ->orderBy('frame_index', 'ASC')
            ->findAll();
    }

    public function find(int $frameId): ?array
    {
        return $this->frames->find($frameId);
    }

    public function create(int $sequenceId, array $payload): int
    {
        $frameJson = $this->normalizeFrameJson($payload['frame_json'] ?? null);
        if ($frameJson === null) {
            return 0;
        }

        $last = $this->frames
            ->select('frame_index')
            ->where('tactical_sequence_id', $sequenceId)
            ->orderBy('frame_index', 'DESC')
            ->first();
        $nextIndex = $last ? ((int) $last['frame_index'] + 1) : 0;

        return (int) $this->frames->insert([
            'tactical_sequence_id' => $sequenceId,
            'frame_index' => $nextIndex,
            'duration_ms' => $this->normalizeDurationMs($payload['duration_ms'] ?? 500),
            'frame_json' => $frameJson,
        ]);
    }

    public function update(int $frameId, array $payload): bool
    {
        $data = [];
        if (array_key_exists('duration_ms', $payload)) {
            $data['duration_ms'] = $this->normalizeDurationMs($payload['duration_ms']);
        }
        if (array_key_exists('frame_json', $payload)) {
            $frameJson = $this->normalizeFrameJson($payload['frame_json']);
            if ($frameJson === null) {
                return false;
            }
            $data['frame_json'] = $frameJson;
        }

        if ($data === []) {
            return true;
        }

        return $this->frames->update($frameId, $data);
    }

    public function delete(int $frameId): bool
    {
        $frame = $this->find($frameId);
        if (!$frame) {
            return false;
        }
        $sequenceId = (int) $frame['tactical_sequence_id'];
        $ok = $this->frames->delete($frameId);
        if (!$ok) {
            return false;
        }

        $this->reindex($sequenceId);
        return true;
    }

    public function saveAll(int $sequenceId, $fps, array $frames): bool
    {
        if (count($frames) < 1) {
            return false;
        }

        $payloadSize = strlen(json_encode($frames));
        if ($payloadSize > 1048576) {
            return false;
        }

        $this->db->transStart();

        $this->frames->where('tactical_sequence_id', $sequenceId)->delete();

        $index = 0;
        foreach ($frames as $frame) {
            $frameJson = $this->normalizeFrameJson($frame['frame_json'] ?? null);
            if ($frameJson === null) {
                $this->db->transRollback();
                return false;
            }

            $this->frames->insert([
                'tactical_sequence_id' => $sequenceId,
                'frame_index' => $index,
                'duration_ms' => $this->normalizeDurationMs($frame['duration_ms'] ?? 500),
                'frame_json' => $frameJson,
            ]);
            $index++;
        }

        $this->sequences->update($sequenceId, ['fps' => $this->normalizeFps($fps)]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    protected function reindex(int $sequenceId): void
    {
        $items = $this->listBySequence($sequenceId);
        $index = 0;
        foreach ($items as $item) {
            if ((int) $item['frame_index'] === $index) {
                $index++;
                continue;
            }
            $this->frames->update((int) $item['id'], ['frame_index' => $index]);
            $index++;
        }
    }

    protected function normalizeFrameJson($frameJson): ?string
    {
        if (is_string($frameJson)) {
            $decoded = json_decode($frameJson, true);
        } else {
            $decoded = $frameJson;
        }

        if (!is_array($decoded)) {
            return null;
        }

        if (!array_key_exists('items', $decoded) || !is_array($decoded['items'])) {
            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    }

    protected function normalizeDurationMs($value): int
    {
        $duration = (int) $value;
        if ($duration < 100) {
            $duration = 100;
        }
        if ($duration > 10000) {
            $duration = 10000;
        }
        return $duration;
    }

    protected function normalizeFps($fps): int
    {
        $value = (int) $fps;
        if ($value < 1) {
            $value = 1;
        }
        if ($value > 10) {
            $value = 10;
        }
        return $value;
    }
}

