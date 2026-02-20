<?php

namespace App\Validation;

class TeamCategoryRules
{
    public function categoryIsActive(string $str, ?string $fields = null, array $data = []): bool
    {
        $categoryId = (int) $str;
        if ($categoryId <= 0) {
            return false;
        }

        return db_connect()->table('categories')
            ->where('id', $categoryId)
            ->where('deleted_at', null)
            ->where('status', 'active')
            ->countAllResults() === 1;
    }

    public function athleteExists($str, ?string $fields = null, array $data = []): bool
    {
        $athleteId = (int) $str;
        if ($athleteId <= 0) {
            return false;
        }

        return db_connect()->table('athletes')
            ->where('id', $athleteId)
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    public function guardianExists($str, ?string $fields = null, array $data = []): bool
    {
        $guardianId = (int) $str;
        if ($guardianId <= 0) {
            return false;
        }

        return db_connect()->table('guardians')
            ->where('id', $guardianId)
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    public function teamExists($str, ?string $fields = null, array $data = []): bool
    {
        $teamId = (int) $str;
        if ($teamId <= 0) {
            return false;
        }

        return db_connect()->table('teams')
            ->where('id', $teamId)
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    public function categoryExists($str, ?string $fields = null, array $data = []): bool
    {
        $categoryId = (int) $str;
        if ($categoryId <= 0) {
            return false;
        }

        return db_connect()->table('categories')
            ->where('id', $categoryId)
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    public function eventExists($str, ?string $fields = null, array $data = []): bool
    {
        $eventId = (int) $str;
        if ($eventId <= 0) {
            return false;
        }

        return db_connect()->table('events')
            ->where('id', $eventId)
            ->where('deleted_at', null)
            ->countAllResults() === 1;
    }

    public function documentOwnerRequired($str, ?string $fields = null, array $data = []): bool
    {
        $athleteId = isset($data['athlete_id']) ? (int) $data['athlete_id'] : 0;
        $guardianId = isset($data['guardian_id']) ? (int) $data['guardian_id'] : 0;
        $teamId = ($str !== null && $str !== '')
            ? (int) $str
            : (isset($data['team_id']) ? (int) $data['team_id'] : 0);
        $athleteName = isset($data['athlete_name']) ? trim((string) $data['athlete_name']) : '';

        return $athleteId > 0 || $guardianId > 0 || $teamId > 0 || $athleteName !== '';
    }

    public function playersRangeValid(string $str, ?string $fields = null, array $data = []): bool
    {
        $min = (isset($data['players_min']) && $data['players_min'] !== '') ? (int) $data['players_min'] : null;
        $max = (isset($data['players_max']) && $data['players_max'] !== '') ? (int) $data['players_max'] : null;
        if ($min === null || $max === null) {
            return true;
        }

        return $min <= $max;
    }

    public function ratingRangeValid(string $str, ?string $fields = null, array $data = []): bool
    {
        if ($str === '') {
            return true;
        }
        $rating = (int) $str;
        return $rating >= 1 && $rating <= 10;
    }

    public function trainingSessionAthleteValid($str, $fields = null, array $data = [], string &$error = null): bool
    {
        if (empty($data)) {
            $request = service('request');
            $post = $request->getPost();
            if (is_array($post) && !empty($post)) {
                $data = $post;
            } else {
                $raw = $request->getRawInput();
                if (is_array($raw)) {
                    $data = $raw;
                }
            }
        }

        $sessionId = isset($data['training_session_id']) ? (int) $data['training_session_id'] : 0;
        if ($sessionId <= 0 && is_scalar($fields) && ctype_digit((string) $fields)) {
            $sessionId = (int) $fields;
        }
        $athleteId = (int) ($data['athlete_id'] ?? $str ?? 0);

        if ($sessionId <= 0 || $athleteId <= 0) {
            $error = 'Payload incompleto';
            return false;
        }

        $db = db_connect();

        $existingLink = $db->table('training_session_athletes')
            ->where('training_session_id', $sessionId)
            ->where('athlete_id', $athleteId)
            ->countAllResults();
        if ($existingLink > 0) {
            return true;
        }

        $session = $db->table('training_sessions')
            ->select('id')
            ->where('id', $sessionId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();
        if (!$session) {
            $error = 'Sessão inválida';
            return false;
        }

        $athlete = $db->table('athletes')
            ->select('id')
            ->where('id', $athleteId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();
        if (!$athlete) {
            $error = 'Atleta inválido';
            return false;
        }

        return true;
    }

    public function teamNameUnique(string $str, ?string $fields = null, array $data = []): bool
    {
        $builder = db_connect()->table('teams')
            ->where('name', $str)
            ->where('deleted_at', null);

        $ignoreId = ($fields !== null && $fields !== '') ? (int) $fields : 0;
        if ($ignoreId > 0) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() === 0;
    }

    public function categoryNameUnique(string $str, ?string $fields = null, array $data = []): bool
    {
        $builder = db_connect()->table('categories')
            ->where('name', $str)
            ->where('deleted_at', null);

        $parts = $fields ? array_map('trim', explode(',', $fields)) : [];
        $teamId = $parts[0] ?? '';
        $ignoreId = $parts[1] ?? '';

        $teamId = $teamId !== '' ? (int) $teamId : (int) ($data['team_id'] ?? 0);
        if ($teamId > 0) {
            $builder->where('team_id', $teamId);
        }

        $ignoreId = $ignoreId !== '' ? (int) $ignoreId : 0;
        if ($ignoreId > 0) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() === 0;
    }
}
