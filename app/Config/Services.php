<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('auth');
        }

        return new \App\Services\AuthService();
    }

    public static function rbac(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('rbac');
        }

        return new \App\Services\RbacService();
    }

    public static function audit(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('audit');
        }

        return new \App\Services\AuditService();
    }

    public static function teams(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('teams');
        }

        return new \App\Services\TeamService();
    }

    public static function categories(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('categories');
        }

        return new \App\Services\CategoryService();
    }

    public static function athletes(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('athletes');
        }

        return new \App\Services\AthleteService();
    }

    public static function guardians(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('guardians');
        }

        return new \App\Services\GuardianService();
    }

    public static function athleteGuardians(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('athleteGuardians');
        }

        return new \App\Services\AthleteGuardianService();
    }

    public static function events(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('events');
        }

        return new \App\Services\EventService();
    }

    public static function eventParticipants(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('eventParticipants');
        }

        return new \App\Services\EventParticipantService();
    }

    public static function attendance(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('attendance');
        }

        return new \App\Services\AttendanceService();
    }

    public static function notices(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('notices');
        }

        return new \App\Services\NoticeService();
    }

    public static function noticeReads(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('noticeReads');
        }

        return new \App\Services\NoticeReadService();
    }

    public static function noticeReplies(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('noticeReplies');
        }

        return new \App\Services\NoticeReplyService();
    }

    public static function noticeNotifications(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('noticeNotifications');
        }

        return new \App\Services\NoticeNotificationService();
    }

    public static function documentTypes(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('documentTypes');
        }

        return new \App\Services\DocumentTypeService();
    }

    public static function documents(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('documents');
        }

        return new \App\Services\DocumentService();
    }

    public static function documentAlerts(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('documentAlerts');
        }

        return new \App\Services\DocumentAlertService();
    }

    public static function exercises(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('exercises');
        }

        return new \App\Services\ExerciseService();
    }

    public static function trainingPlans(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('trainingPlans');
        }

        return new \App\Services\TrainingPlanService();
    }

    public static function trainingPlanBlocks(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('trainingPlanBlocks');
        }

        return new \App\Services\TrainingPlanBlockService();
    }

    public static function trainingSessions(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('trainingSessions');
        }

        return new \App\Services\TrainingSessionService();
    }

    public static function trainingSessionAthletes(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('trainingSessionAthletes');
        }

        return new \App\Services\TrainingSessionAthleteService();
    }

    public static function matches(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matches');
        }

        return new \App\Services\MatchService();
    }

    public static function matchCallups(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matchCallups');
        }

        return new \App\Services\MatchCallupService();
    }

    public static function matchLineups(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matchLineups');
        }

        return new \App\Services\MatchLineupService();
    }

    public static function matchEvents(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matchEvents');
        }

        return new \App\Services\MatchEventService();
    }

    public static function matchReports(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matchReports');
        }

        return new \App\Services\MatchReportService();
    }

    public static function matchAttachments(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('matchAttachments');
        }

        return new \App\Services\MatchAttachmentService();
    }

    public static function dashboards(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('dashboards');
        }

        return new \App\Services\DashboardService();
    }

    public static function reports(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('reports');
        }

        return new \App\Services\ReportService();
    }

    public static function exports(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('exports');
        }

        return new \App\Services\ExportService();
    }
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
