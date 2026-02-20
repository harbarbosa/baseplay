<?php

namespace App\Services;

use App\Models\NoticeReplyModel;
use CodeIgniter\I18n\Time;

class NoticeReplyService
{
    protected NoticeReplyModel $replies;

    public function __construct()
    {
        $this->replies = new NoticeReplyModel();
    }

    public function create(int $noticeId, int $userId, string $message): int
    {
        return (int) $this->replies->insert([
            'notice_id' => $noticeId,
            'user_id' => $userId,
            'message' => $message,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
    }

    public function listByNotice(int $noticeId): array
    {
        return $this->replies->select('notice_replies.*, users.name AS user_name')
            ->join('users', 'users.id = notice_replies.user_id', 'left')
            ->where('notice_replies.notice_id', $noticeId)
            ->orderBy('notice_replies.created_at', 'DESC')
            ->findAll();
    }
}