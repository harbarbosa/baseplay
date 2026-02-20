<?php

namespace App\Services;

use App\Models\NoticeReadModel;
use CodeIgniter\I18n\Time;

class NoticeReadService
{
    protected NoticeReadModel $reads;

    public function __construct()
    {
        $this->reads = new NoticeReadModel();
    }

    public function markRead(int $noticeId, int $userId): int
    {
        $existing = $this->reads->where('notice_id', $noticeId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return (int) $existing['id'];
        }

        return (int) $this->reads->insert([
            'notice_id' => $noticeId,
            'user_id' => $userId,
            'read_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function isRead(int $noticeId, int $userId): bool
    {
        return $this->reads->where('notice_id', $noticeId)
            ->where('user_id', $userId)
            ->countAllResults() > 0;
    }

    public function listReaders(int $noticeId): array
    {
        return $this->reads->select('notice_reads.*, users.name AS user_name, users.email AS user_email')
            ->join('users', 'users.id = notice_reads.user_id', 'left')
            ->where('notice_reads.notice_id', $noticeId)
            ->orderBy('notice_reads.read_at', 'DESC')
            ->findAll();
    }
}