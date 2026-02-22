<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index', ['filter' => 'permission:dashboard.view']);

$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login', ['filter' => 'csrf']);
$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);

$routes->get('password/forgot', 'Password::forgotForm');
$routes->post('password/forgot', 'Password::sendReset', ['filter' => 'csrf']);
$routes->get('password/reset/(:segment)', 'Password::resetForm/$1');
$routes->post('password/reset', 'Password::reset', ['filter' => 'csrf']);

$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    $routes->get('users', 'Admin\\Users::index', ['filter' => 'permission:users.manage']);
    $routes->get('users/create', 'Admin\\Users::create', ['filter' => 'permission:users.manage']);
    $routes->post('users', 'Admin\\Users::store', ['filter' => ['csrf', 'permission:users.manage']]);

    $routes->get('roles', 'Admin\\Roles::index', ['filter' => 'permission:roles.manage']);
    $routes->get('roles/create', 'Admin\\Roles::create', ['filter' => 'permission:roles.manage']);
    $routes->post('roles', 'Admin\\Roles::store', ['filter' => ['csrf', 'permission:roles.manage']]);
    $routes->get('roles/(:num)/edit', 'Admin\\Roles::edit/$1', ['filter' => 'permission:roles.manage']);
    $routes->post('roles/(:num)/update', 'Admin\\Roles::update/$1', ['filter' => ['csrf', 'permission:roles.manage']]);
    $routes->post('roles/(:num)/delete', 'Admin\\Roles::delete/$1', ['filter' => ['csrf', 'permission:roles.manage']]);
});

$routes->group('api', static function ($routes) {
    $routes->options('(:any)', 'Api\\PreflightController::index/$1');
    $routes->post('auth/login', 'Api\\AuthController::login');
    $routes->get('auth/me', 'Api\\AuthController::me', ['filter' => 'apiauth']);
    $routes->get('users', 'Api\\UsersController::index', ['filter' => 'apiauth']);

    $routes->get('dashboard/admin', 'Api\\DashboardController::admin', ['filter' => 'apiauth']);
    $routes->get('dashboard/trainer', 'Api\\DashboardController::trainer', ['filter' => 'apiauth']);
    $routes->get('dashboard/assistant', 'Api\\DashboardController::assistant', ['filter' => 'apiauth']);
    $routes->get('dashboard/athlete', 'Api\\DashboardController::athlete', ['filter' => 'apiauth']);

    $routes->get('reports/attendance', 'Api\\ReportsController::attendance', ['filter' => 'apiauth']);
    $routes->get('reports/trainings', 'Api\\ReportsController::trainings', ['filter' => 'apiauth']);
    $routes->get('reports/matches', 'Api\\ReportsController::matches', ['filter' => 'apiauth']);
    $routes->get('reports/documents', 'Api\\ReportsController::documents', ['filter' => 'apiauth']);
    $routes->get('reports/athlete/(:num)', 'Api\\ReportsController::athlete/$1', ['filter' => 'apiauth']);

    $routes->get('teams', 'Api\\TeamsController::index', ['filter' => 'apiauth']);
    $routes->post('teams', 'Api\\TeamsController::store', ['filter' => 'apiauth']);
    $routes->get('teams/(:num)', 'Api\\TeamsController::show/$1', ['filter' => 'apiauth']);
    $routes->put('teams/(:num)', 'Api\\TeamsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('teams/(:num)', 'Api\\TeamsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('teams/(:num)/categories', 'Api\\TeamsController::categories/$1', ['filter' => 'apiauth']);
    $routes->post('teams/(:num)/categories', 'Api\\TeamsController::storeCategory/$1', ['filter' => 'apiauth']);
    $routes->get('categories/(:num)', 'Api\\CategoriesController::show/$1', ['filter' => 'apiauth']);
    $routes->put('categories/(:num)', 'Api\\CategoriesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('categories/(:num)', 'Api\\CategoriesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('athletes', 'Api\\AthletesController::index', ['filter' => 'apiauth']);
    $routes->post('athletes', 'Api\\AthletesController::store', ['filter' => 'apiauth']);
    $routes->get('athletes/(:num)', 'Api\\AthletesController::show/$1', ['filter' => 'apiauth']);
    $routes->get('athletes/(:num)/summary/last-activity', 'Api\\AthletesController::lastActivity/$1', ['filter' => 'apiauth']);
    $routes->put('athletes/(:num)', 'Api\\AthletesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('athletes/(:num)', 'Api\\AthletesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('athletes/(:num)/guardians', 'Api\\AthletesController::guardians/$1', ['filter' => 'apiauth']);
    $routes->post('athletes/(:num)/guardians', 'Api\\AthletesController::linkGuardian/$1', ['filter' => 'apiauth']);

    $routes->get('guardians', 'Api\\GuardiansController::index', ['filter' => 'apiauth']);
    $routes->post('guardians', 'Api\\GuardiansController::store', ['filter' => 'apiauth']);
    $routes->get('guardians/(:num)', 'Api\\GuardiansController::show/$1', ['filter' => 'apiauth']);
    $routes->put('guardians/(:num)', 'Api\\GuardiansController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('guardians/(:num)', 'Api\\GuardiansController::delete/$1', ['filter' => 'apiauth']);

    $routes->put('athlete-guardians/(:num)', 'Api\\AthleteGuardiansController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('athlete-guardians/(:num)', 'Api\\AthleteGuardiansController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('events', 'Api\\EventsController::index', ['filter' => 'apiauth']);
    $routes->post('events', 'Api\\EventsController::store', ['filter' => 'apiauth']);
    $routes->get('events/(:num)', 'Api\\EventsController::show/$1', ['filter' => 'apiauth']);
    $routes->put('events/(:num)', 'Api\\EventsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('events/(:num)', 'Api\\EventsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('events/(:num)/participants', 'Api\\EventsController::participants/$1', ['filter' => 'apiauth']);
    $routes->post('events/(:num)/participants', 'Api\\EventParticipantsController::store/$1', ['filter' => 'apiauth']);
    $routes->put('event-participants/(:num)', 'Api\\EventParticipantsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('event-participants/(:num)', 'Api\\EventParticipantsController::delete/$1', ['filter' => 'apiauth']);
    $routes->post('events/(:num)/confirm', 'Api\\EventsController::confirm/$1', ['filter' => 'apiauth']);

    $routes->get('events/(:num)/attendance', 'Api\\AttendanceController::index/$1', ['filter' => 'apiauth']);
    $routes->post('events/(:num)/attendance', 'Api\\AttendanceController::store/$1', ['filter' => 'apiauth']);
    $routes->put('attendance/(:num)', 'Api\\AttendanceController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('attendance/(:num)', 'Api\\AttendanceController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('notices', 'Api\\NoticesController::index', ['filter' => 'apiauth']);
    $routes->post('notices', 'Api\\NoticesController::store', ['filter' => 'apiauth']);
    $routes->get('notices/(:num)', 'Api\\NoticesController::show/$1', ['filter' => 'apiauth']);
    $routes->put('notices/(:num)', 'Api\\NoticesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('notices/(:num)', 'Api\\NoticesController::delete/$1', ['filter' => 'apiauth']);
    $routes->post('notices/(:num)/read', 'Api\\NoticesController::read/$1', ['filter' => 'apiauth']);
    $routes->get('notices/(:num)/replies', 'Api\\NoticesController::replies/$1', ['filter' => 'apiauth']);
    $routes->post('notices/(:num)/reply', 'Api\\NoticesController::reply/$1', ['filter' => 'apiauth']);

    $routes->get('documents', 'Api\\DocumentsController::index', ['filter' => 'apiauth']);
    $routes->post('documents', 'Api\\DocumentsController::store', ['filter' => 'apiauth']);
    $routes->get('documents/(:num)', 'Api\\DocumentsController::show/$1', ['filter' => 'apiauth']);
    $routes->put('documents/(:num)', 'Api\\DocumentsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('documents/(:num)', 'Api\\DocumentsController::delete/$1', ['filter' => 'apiauth']);
    $routes->get('documents/missing-required', 'Api\\DocumentsController::missingRequired', ['filter' => 'apiauth']);
    $routes->get('documents/alerts', 'Api\\DocumentAlertsController::index', ['filter' => 'apiauth']);
    $routes->get('alerts', 'Api\\AlertsController::index', ['filter' => 'apiauth']);
    $routes->post('alerts/(:num)/read', 'Api\\AlertsController::read/$1', ['filter' => 'apiauth']);

    $routes->get('document-types', 'Api\\DocumentTypesController::index', ['filter' => 'apiauth']);
    $routes->post('document-types', 'Api\\DocumentTypesController::store', ['filter' => 'apiauth']);
    $routes->put('document-types/(:num)', 'Api\\DocumentTypesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('document-types/(:num)', 'Api\\DocumentTypesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('exercises', 'Api\\ExercisesController::index', ['filter' => 'apiauth']);
    $routes->post('exercises', 'Api\\ExercisesController::store', ['filter' => 'apiauth']);
    $routes->get('exercises/(:num)', 'Api\\ExercisesController::show/$1', ['filter' => 'apiauth']);
    $routes->put('exercises/(:num)', 'Api\\ExercisesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('exercises/(:num)', 'Api\\ExercisesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('exercise-tags', 'Api\\ExerciseTagsController::index', ['filter' => 'apiauth']);
    $routes->post('exercise-tags', 'Api\\ExerciseTagsController::store', ['filter' => 'apiauth']);

    $routes->get('training-plans', 'Api\\TrainingPlansController::index', ['filter' => 'apiauth']);
    $routes->post('training-plans', 'Api\\TrainingPlansController::store', ['filter' => 'apiauth']);
    $routes->get('training-plans/(:num)', 'Api\\TrainingPlansController::show/$1', ['filter' => 'apiauth']);
    $routes->put('training-plans/(:num)', 'Api\\TrainingPlansController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('training-plans/(:num)', 'Api\\TrainingPlansController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('training-plans/(:num)/blocks', 'Api\\TrainingPlansController::blocks/$1', ['filter' => 'apiauth']);
    $routes->post('training-plans/(:num)/blocks', 'Api\\TrainingPlansController::storeBlock/$1', ['filter' => 'apiauth']);
    $routes->put('training-plan-blocks/(:num)', 'Api\\TrainingPlanBlocksController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('training-plan-blocks/(:num)', 'Api\\TrainingPlanBlocksController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('training-sessions', 'Api\\TrainingSessionsController::index', ['filter' => 'apiauth']);
    $routes->post('training-sessions', 'Api\\TrainingSessionsController::store', ['filter' => 'apiauth']);
    $routes->get('training-sessions/(:num)', 'Api\\TrainingSessionsController::show/$1', ['filter' => 'apiauth']);
    $routes->put('training-sessions/(:num)', 'Api\\TrainingSessionsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('training-sessions/(:num)', 'Api\\TrainingSessionsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('training-sessions/(:num)/athletes', 'Api\\TrainingSessionsController::athletes/$1', ['filter' => 'apiauth']);
    $routes->post('training-sessions/(:num)/athletes', 'Api\\TrainingSessionAthletesController::store/$1', ['filter' => 'apiauth']);
    $routes->put('training-session-athletes/(:num)', 'Api\\TrainingSessionAthletesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('training-session-athletes/(:num)', 'Api\\TrainingSessionAthletesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('matches', 'Api\\MatchesController::index', ['filter' => 'apiauth']);
    $routes->post('matches', 'Api\\MatchesController::store', ['filter' => 'apiauth']);
    $routes->get('matches/(:num)', 'Api\\MatchesController::show/$1', ['filter' => 'apiauth']);
    $routes->put('matches/(:num)', 'Api\\MatchesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('matches/(:num)', 'Api\\MatchesController::delete/$1', ['filter' => 'apiauth']);
    $routes->post('matches/from-event/(:num)', 'Api\\MatchesController::fromEvent/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/confirm', 'Api\\MatchesController::confirm/$1', ['filter' => 'apiauth']);

    $routes->get('matches/(:num)/callups', 'Api\\MatchCallupsController::index/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/callups', 'Api\\MatchCallupsController::store/$1', ['filter' => 'apiauth']);
    $routes->put('match-callups/(:num)', 'Api\\MatchCallupsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('match-callups/(:num)', 'Api\\MatchCallupsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('matches/(:num)/lineup', 'Api\\MatchLineupController::index/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/lineup', 'Api\\MatchLineupController::store/$1', ['filter' => 'apiauth']);
    $routes->put('match-lineup/(:num)', 'Api\\MatchLineupController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('match-lineup/(:num)', 'Api\\MatchLineupController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('matches/(:num)/events', 'Api\\MatchEventsController::index/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/events', 'Api\\MatchEventsController::store/$1', ['filter' => 'apiauth']);
    $routes->put('match-events/(:num)', 'Api\\MatchEventsController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('match-events/(:num)', 'Api\\MatchEventsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('matches/(:num)/report', 'Api\\MatchReportsController::show/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/report', 'Api\\MatchReportsController::store/$1', ['filter' => 'apiauth']);

    $routes->get('matches/(:num)/attachments', 'Api\\MatchAttachmentsController::index/$1', ['filter' => 'apiauth']);
    $routes->post('matches/(:num)/attachments', 'Api\\MatchAttachmentsController::store/$1', ['filter' => 'apiauth']);
    $routes->delete('match-attachments/(:num)', 'Api\\MatchAttachmentsController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('tactical-boards/(:num)/sequences', 'Api\\TacticalSequencesController::index/$1', ['filter' => 'apiauth']);
    $routes->post('tactical-boards/(:num)/sequences', 'Api\\TacticalSequencesController::store/$1', ['filter' => 'apiauth']);
    $routes->get('tactical-sequences/(:num)', 'Api\\TacticalSequencesController::show/$1', ['filter' => 'apiauth']);
    $routes->put('tactical-sequences/(:num)', 'Api\\TacticalSequencesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('tactical-sequences/(:num)', 'Api\\TacticalSequencesController::delete/$1', ['filter' => 'apiauth']);

    $routes->get('tactical-sequences/(:num)/frames', 'Api\\TacticalSequenceFramesController::index/$1', ['filter' => 'apiauth']);
    $routes->post('tactical-sequences/(:num)/frames', 'Api\\TacticalSequenceFramesController::store/$1', ['filter' => 'apiauth']);
    $routes->put('tactical-sequence-frames/(:num)', 'Api\\TacticalSequenceFramesController::update/$1', ['filter' => 'apiauth']);
    $routes->delete('tactical-sequence-frames/(:num)', 'Api\\TacticalSequenceFramesController::delete/$1', ['filter' => 'apiauth']);
    $routes->post('tactical-sequences/(:num)/save-all', 'Api\\TacticalSequenceFramesController::saveAll/$1', ['filter' => 'apiauth']);
});

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('squad', 'Squad::index', ['filter' => 'permission:athletes.view']);
    $routes->get('overview/squad', 'Squad::index', ['filter' => 'permission:athletes.view']);
    $routes->get('ops', 'Ops::index', ['filter' => 'permission:events.view']);
    $routes->get('events/overview', 'Ops::index', ['filter' => 'permission:events.view']);

    $routes->get('teams', 'Teams::index', ['filter' => 'permission:teams.view']);
    $routes->get('teams/create', 'Teams::create', ['filter' => 'permission:teams.create']);
    $routes->post('teams', 'Teams::store', ['filter' => ['csrf', 'permission:teams.create']]);
    $routes->get('teams/(:num)', 'Teams::show/$1', ['filter' => ['permission:teams.view', 'permission:categories.view']]);
    $routes->get('teams/(:num)/edit', 'Teams::edit/$1', ['filter' => 'permission:teams.update']);
    $routes->post('teams/(:num)/update', 'Teams::update/$1', ['filter' => ['csrf', 'permission:teams.update']]);
    $routes->get('teams/(:num)/delete', 'Teams::deleteConfirm/$1', ['filter' => 'permission:teams.delete']);
    $routes->post('teams/(:num)/delete', 'Teams::delete/$1', ['filter' => ['csrf', 'permission:teams.delete']]);

    $routes->get('teams/(:num)/categories/create', 'Categories::create/$1', ['filter' => 'permission:categories.create']);
    $routes->post('teams/(:num)/categories', 'Categories::store/$1', ['filter' => ['csrf', 'permission:categories.create']]);
    $routes->get('categories/(:num)/edit', 'Categories::edit/$1', ['filter' => 'permission:categories.update']);
    $routes->post('categories/(:num)/update', 'Categories::update/$1', ['filter' => ['csrf', 'permission:categories.update']]);
    $routes->get('categories/(:num)/delete', 'Categories::deleteConfirm/$1', ['filter' => 'permission:categories.delete']);
    $routes->post('categories/(:num)/delete', 'Categories::delete/$1', ['filter' => ['csrf', 'permission:categories.delete']]);

    $routes->get('athletes', 'Athletes::index', ['filter' => 'permission:athletes.view']);
    $routes->get('athletes/create', 'Athletes::create', ['filter' => 'permission:athletes.create']);
    $routes->post('athletes', 'Athletes::store', ['filter' => ['csrf', 'permission:athletes.create']]);
    $routes->get('athletes/(:num)', 'Athletes::show/$1', ['filter' => ['permission:athletes.view', 'permission:guardians.view']]);
    $routes->get('athletes/(:num)/edit', 'Athletes::edit/$1', ['filter' => 'permission:athletes.update']);
    $routes->post('athletes/(:num)/update', 'Athletes::update/$1', ['filter' => ['csrf', 'permission:athletes.update']]);
    $routes->get('athletes/(:num)/delete', 'Athletes::deleteConfirm/$1', ['filter' => 'permission:athletes.delete']);
    $routes->post('athletes/(:num)/delete', 'Athletes::delete/$1', ['filter' => ['csrf', 'permission:athletes.delete']]);

    $routes->post('athletes/(:num)/guardians/link', 'Athletes::linkGuardian/$1', ['filter' => ['csrf', 'permission:guardians.create']]);
    $routes->post('athletes/(:num)/guardians/create-link', 'Athletes::createGuardianAndLink/$1', ['filter' => ['csrf', 'permission:guardians.create']]);
    $routes->post('athlete-guardians/(:num)/update', 'Athletes::updateLink/$1', ['filter' => ['csrf', 'permission:guardians.update']]);
    $routes->post('athlete-guardians/(:num)/delete', 'Athletes::unlinkGuardian/$1', ['filter' => ['csrf', 'permission:guardians.delete']]);

    $routes->get('guardians', 'Guardians::index', ['filter' => 'permission:guardians.view']);
    $routes->get('guardians/create', 'Guardians::create', ['filter' => 'permission:guardians.create']);
    $routes->post('guardians', 'Guardians::store', ['filter' => ['csrf', 'permission:guardians.create']]);
    $routes->get('guardians/(:num)', 'Guardians::show/$1', ['filter' => 'permission:guardians.view']);
    $routes->get('guardians/(:num)/edit', 'Guardians::edit/$1', ['filter' => 'permission:guardians.update']);
    $routes->post('guardians/(:num)/update', 'Guardians::update/$1', ['filter' => ['csrf', 'permission:guardians.update']]);
    $routes->get('guardians/(:num)/delete', 'Guardians::deleteConfirm/$1', ['filter' => 'permission:guardians.delete']);
    $routes->post('guardians/(:num)/delete', 'Guardians::delete/$1', ['filter' => ['csrf', 'permission:guardians.delete']]);

    $routes->get('events', 'Events::index', ['filter' => 'permission:events.view']);
    $routes->get('events/create', 'Events::create', ['filter' => 'permission:events.create']);
    $routes->post('events', 'Events::store', ['filter' => ['csrf', 'permission:events.create']]);
    $routes->get('events/(:num)', 'Events::show/$1', ['filter' => 'permission:events.view']);
    $routes->get('events/(:num)/edit', 'Events::edit/$1', ['filter' => 'permission:events.update']);
    $routes->post('events/(:num)/update', 'Events::update/$1', ['filter' => ['csrf', 'permission:events.update']]);
    $routes->get('events/(:num)/delete', 'Events::deleteConfirm/$1', ['filter' => 'permission:events.delete']);
    $routes->post('events/(:num)/delete', 'Events::delete/$1', ['filter' => ['csrf', 'permission:events.delete']]);

    $routes->post('events/(:num)/participants/add-category', 'Events::addParticipantsCategory/$1', ['filter' => ['csrf', 'permission:invitations.manage']]);
    $routes->post('events/(:num)/participants/add', 'Events::addParticipant/$1', ['filter' => ['csrf', 'permission:invitations.manage']]);
    $routes->post('event-participants/(:num)/update', 'Events::updateParticipant/$1', ['filter' => ['csrf', 'permission:invitations.manage']]);
    $routes->post('event-participants/(:num)/delete', 'Events::deleteParticipant/$1', ['filter' => ['csrf', 'permission:invitations.manage']]);

    $routes->post('events/(:num)/attendance', 'Events::markAttendance/$1', ['filter' => ['csrf', 'permission:attendance.manage']]);

    $routes->get('notices', 'Notices::index', ['filter' => 'permission:notices.view']);
    $routes->get('notices/create', 'Notices::create', ['filter' => 'permission:notices.create']);
    $routes->post('notices', 'Notices::store', ['filter' => ['csrf', 'permission:notices.create']]);
    $routes->get('notices/(:num)', 'Notices::show/$1', ['filter' => 'permission:notices.view']);
    $routes->get('notices/(:num)/edit', 'Notices::edit/$1', ['filter' => 'permission:notices.update']);
    $routes->post('notices/(:num)/update', 'Notices::update/$1', ['filter' => ['csrf', 'permission:notices.update']]);
    $routes->get('notices/(:num)/delete', 'Notices::deleteConfirm/$1', ['filter' => 'permission:notices.delete']);
    $routes->post('notices/(:num)/delete', 'Notices::delete/$1', ['filter' => ['csrf', 'permission:notices.delete']]);
    $routes->post('notices/(:num)/read', 'Notices::markRead/$1', ['filter' => ['csrf', 'permission:notices.view']]);
    $routes->post('notices/(:num)/reply', 'Notices::reply/$1', ['filter' => ['csrf', 'permission:notices.view']]);

    $routes->get('documents', 'Documents::index', ['filter' => 'permission:documents.view']);
    $routes->get('documents/overview', 'Documents::overview', ['filter' => 'permission:documents.view']);
    $routes->post('documents/export-pending', 'Documents::exportPendingCsv', ['filter' => 'permission:documents.view']);
    $routes->get('documents/create', 'Documents::create', ['filter' => 'permission:documents.upload']);
    $routes->post('documents', 'Documents::store', ['filter' => ['csrf', 'permission:documents.upload']]);
    $routes->get('documents/(:num)', 'Documents::show/$1', ['filter' => 'permission:documents.view']);
    $routes->get('documents/(:num)/download', 'Documents::download/$1', ['filter' => 'permission:documents.view']);
    $routes->get('documents/(:num)/edit', 'Documents::edit/$1', ['filter' => 'permission:documents.update']);
    $routes->post('documents/(:num)/update', 'Documents::update/$1', ['filter' => ['csrf', 'permission:documents.update']]);
    $routes->get('documents/(:num)/delete', 'Documents::deleteConfirm/$1', ['filter' => 'permission:documents.delete']);
    $routes->post('documents/(:num)/delete', 'Documents::delete/$1', ['filter' => ['csrf', 'permission:documents.delete']]);

    $routes->get('document-types', 'DocumentTypes::index', ['filter' => 'permission:document_types.manage']);
    $routes->get('document-types/create', 'DocumentTypes::create', ['filter' => 'permission:document_types.manage']);
    $routes->post('document-types', 'DocumentTypes::store', ['filter' => ['csrf', 'permission:document_types.manage']]);
    $routes->get('document-types/(:num)/edit', 'DocumentTypes::edit/$1', ['filter' => 'permission:document_types.manage']);
    $routes->post('document-types/(:num)/update', 'DocumentTypes::update/$1', ['filter' => ['csrf', 'permission:document_types.manage']]);
    $routes->get('document-types/(:num)/delete', 'DocumentTypes::deleteConfirm/$1', ['filter' => 'permission:document_types.manage']);
    $routes->post('document-types/(:num)/delete', 'DocumentTypes::delete/$1', ['filter' => ['csrf', 'permission:document_types.manage']]);

    $routes->get('exercises', 'Exercises::index', ['filter' => 'permission:exercises.view']);
    $routes->get('exercises/create', 'Exercises::create', ['filter' => 'permission:exercises.create']);
    $routes->post('exercises', 'Exercises::store', ['filter' => ['csrf', 'permission:exercises.create']]);
    $routes->get('exercises/(:num)', 'Exercises::show/$1', ['filter' => 'permission:exercises.view']);
    $routes->post('exercises/(:num)/tactical-boards', 'Exercises::addTacticalBoard/$1', ['filter' => ['csrf', 'permission:exercises.update']]);
    $routes->post('exercises/(:num)/tactical-boards/(:num)/delete', 'Exercises::removeTacticalBoard/$1/$2', ['filter' => ['csrf', 'permission:exercises.update']]);
    $routes->get('exercises/(:num)/edit', 'Exercises::edit/$1', ['filter' => 'permission:exercises.update']);
    $routes->post('exercises/(:num)/update', 'Exercises::update/$1', ['filter' => ['csrf', 'permission:exercises.update']]);
    $routes->get('exercises/(:num)/delete', 'Exercises::deleteConfirm/$1', ['filter' => 'permission:exercises.delete']);
    $routes->post('exercises/(:num)/delete', 'Exercises::delete/$1', ['filter' => ['csrf', 'permission:exercises.delete']]);

    $routes->get('training-plans', 'TrainingPlans::index', ['filter' => 'permission:training_plans.view']);
    $routes->get('training-plans/create', 'TrainingPlans::create', ['filter' => 'permission:training_plans.create']);
    $routes->post('training-plans', 'TrainingPlans::store', ['filter' => ['csrf', 'permission:training_plans.create']]);
    $routes->get('training-plans/(:num)', 'TrainingPlans::show/$1', ['filter' => 'permission:training_plans.view']);
    $routes->get('training-plans/(:num)/edit', 'TrainingPlans::edit/$1', ['filter' => 'permission:training_plans.update']);
    $routes->post('training-plans/(:num)/update', 'TrainingPlans::update/$1', ['filter' => ['csrf', 'permission:training_plans.update']]);
    $routes->get('training-plans/(:num)/delete', 'TrainingPlans::deleteConfirm/$1', ['filter' => 'permission:training_plans.delete']);
    $routes->post('training-plans/(:num)/delete', 'TrainingPlans::delete/$1', ['filter' => ['csrf', 'permission:training_plans.delete']]);
    $routes->post('training-plans/(:num)/blocks', 'TrainingPlans::addBlock/$1', ['filter' => ['csrf', 'permission:training_plans.update']]);
    $routes->post('training-plan-blocks/(:num)/update', 'TrainingPlans::updateBlock/$1', ['filter' => ['csrf', 'permission:training_plans.update']]);
    $routes->get('training-plan-blocks/(:num)/media', 'TrainingPlans::downloadBlockMedia/$1', ['filter' => 'permission:training_plans.view']);
    $routes->get('training-plan-blocks/(:num)/delete', 'TrainingPlans::deleteBlock/$1', ['filter' => 'permission:training_plans.delete']);

    $routes->get('training-sessions', 'TrainingSessions::index', ['filter' => 'permission:training_sessions.view']);
    $routes->get('training-sessions/create', 'TrainingSessions::create', ['filter' => 'permission:training_sessions.create']);
    $routes->get('training-sessions/create-from-event/(:num)', 'TrainingSessions::createFromEvent/$1', ['filter' => 'permission:training_sessions.create']);
    $routes->post('training-sessions', 'TrainingSessions::store', ['filter' => ['csrf', 'permission:training_sessions.create']]);
    $routes->get('training-sessions/(:num)', 'TrainingSessions::show/$1', ['filter' => 'permission:training_sessions.view']);
    $routes->get('training-sessions/(:num)/field', 'TrainingSessions::fieldMode/$1', ['filter' => 'permission:training_sessions.update']);
    $routes->get('training-sessions/(:num)/edit', 'TrainingSessions::edit/$1', ['filter' => 'permission:training_sessions.update']);
    $routes->post('training-sessions/(:num)/update', 'TrainingSessions::update/$1', ['filter' => ['csrf', 'permission:training_sessions.update']]);
    $routes->get('training-sessions/(:num)/delete', 'TrainingSessions::deleteConfirm/$1', ['filter' => 'permission:training_sessions.delete']);
    $routes->post('training-sessions/(:num)/delete', 'TrainingSessions::delete/$1', ['filter' => ['csrf', 'permission:training_sessions.delete']]);
    $routes->post('training-sessions/(:num)/athletes', 'TrainingSessions::saveAthlete/$1', ['filter' => ['csrf', 'permission:training_sessions.update']]);

    $routes->get('tactical-boards', 'TacticalBoards::index', ['filter' => 'permission:tactical_board.view']);
    $routes->get('tactical-boards/templates', 'TacticalBoards::templates', ['filter' => 'permission:templates.view']);
    $routes->get('tactical-boards/templates/create', 'TacticalBoards::templateCreate', ['filter' => 'permission:templates.manage']);
    $routes->post('tactical-boards/templates', 'TacticalBoards::templateStore', ['filter' => ['csrf', 'permission:templates.manage']]);
    $routes->get('tactical-boards/templates/(:num)/edit', 'TacticalBoards::templateEdit/$1', ['filter' => 'permission:templates.manage']);
    $routes->post('tactical-boards/templates/(:num)', 'TacticalBoards::templateUpdate/$1', ['filter' => ['csrf', 'permission:templates.manage']]);
    $routes->post('tactical-boards/templates/(:num)/delete', 'TacticalBoards::templateDelete/$1', ['filter' => ['csrf', 'permission:templates.manage']]);
    $routes->get('tactical-boards/templates/(:num)/editor', 'TacticalBoards::templateEditor/$1', ['filter' => 'permission:templates.manage']);
    $routes->post('tactical-boards/templates/(:num)/save', 'TacticalBoards::templateSave/$1', ['filter' => ['csrf', 'permission:templates.manage']]);
    $routes->get('tactical-boards/create', 'TacticalBoards::create', ['filter' => 'permission:tactical_board.create']);
    $routes->post('tactical-boards', 'TacticalBoards::store', ['filter' => ['csrf', 'permission:tactical_board.create']]);
    $routes->get('tactical-boards/(:num)', 'TacticalBoards::show/$1', ['filter' => 'permission:tactical_board.view']);
    $routes->post('tactical-boards/(:num)/save', 'TacticalBoards::save/$1', ['filter' => ['csrf', 'permission:tactical_board.update']]);
    $routes->post('tactical-boards/(:num)/duplicate', 'TacticalBoards::duplicate/$1', ['filter' => ['csrf', 'permission:tactical_board.create']]);
    $routes->get('tactical-boards/(:num)/states', 'TacticalBoards::states/$1', ['filter' => 'permission:tactical_board.view']);
    $routes->get('tactical-boards/(:num)/load/(:num)', 'TacticalBoards::load/$1/$2', ['filter' => 'permission:tactical_board.view']);
    $routes->post('tactical-boards/(:num)/delete', 'TacticalBoards::delete/$1', ['filter' => ['csrf', 'permission:tactical_board.delete']]);
    $routes->get('tactical-boards/(:num)/sequences/json', 'TacticalBoards::listSequencesJson/$1', ['filter' => 'permission:tactical_board.view']);
    $routes->post('tactical-boards/(:num)/sequences/json', 'TacticalBoards::createSequenceJson/$1', ['filter' => 'permission:tactical_sequence.manage']);
    $routes->post('tactical-sequences/(:num)/update/json', 'TacticalBoards::updateSequenceJson/$1', ['filter' => 'permission:tactical_sequence.manage']);
    $routes->post('tactical-sequences/(:num)/delete/json', 'TacticalBoards::deleteSequenceJson/$1', ['filter' => 'permission:tactical_sequence.manage']);
    $routes->get('tactical-sequences/(:num)/frames/json', 'TacticalBoards::listFramesJson/$1', ['filter' => 'permission:tactical_board.view']);
    $routes->post('tactical-sequences/(:num)/save-all/json', 'TacticalBoards::saveAllFramesJson/$1', ['filter' => 'permission:tactical_sequence.manage']);

    $routes->get('reports/attendance', 'Reports::attendance', ['filter' => 'permission:reports.view']);
    $routes->get('reports/trainings', 'Reports::trainings', ['filter' => 'permission:reports.view']);
    $routes->get('reports/matches', 'Reports::matches', ['filter' => 'permission:reports.view']);
    $routes->get('reports/documents', 'Reports::documents', ['filter' => 'permission:reports.view']);
    $routes->get('reports/athlete/(:num)', 'Reports::athlete/$1', ['filter' => 'permission:reports.view']);
    $routes->get('alerts', 'Alerts::index', ['filter' => 'permission:alerts.view']);
    $routes->post('alerts/(:num)/read', 'Alerts::read/$1', ['filter' => ['csrf', 'permission:alerts.view']]);
    $routes->get('pending-center', 'PendingCenter::index', ['filter' => 'permission:alerts.view']);

    $routes->get('matches', 'Matches::index', ['filter' => 'permission:matches.view']);
    $routes->get('matches/create', 'Matches::create', ['filter' => 'permission:matches.create']);
    $routes->get('matches/create-from-event/(:num)', 'Matches::createFromEvent/$1', ['filter' => 'permission:matches.create']);
    $routes->post('matches', 'Matches::store', ['filter' => ['csrf', 'permission:matches.create']]);
    $routes->get('matches/(:num)', 'Matches::show/$1', ['filter' => 'permission:matches.view']);
    $routes->post('matches/(:num)/tactical-boards', 'Matches::addTacticalBoard/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->post('matches/(:num)/tactical-boards/(:num)/delete', 'Matches::removeTacticalBoard/$1/$2', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->get('matches/(:num)/edit', 'Matches::edit/$1', ['filter' => 'permission:matches.update']);
    $routes->post('matches/(:num)/update', 'Matches::update/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->get('matches/(:num)/delete', 'Matches::deleteConfirm/$1', ['filter' => 'permission:matches.delete']);
    $routes->post('matches/(:num)/delete', 'Matches::delete/$1', ['filter' => ['csrf', 'permission:matches.delete']]);

    $routes->post('matches/(:num)/callups/add-category', 'Matches::addCallupsCategory/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->post('matches/(:num)/callups/import', 'Matches::importCallupsFromEvent/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->post('matches/(:num)/callups/add', 'Matches::addCallup/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->post('match-callups/(:num)/update', 'Matches::updateCallup/$1', ['filter' => ['csrf', 'permission:matches.update']]);
    $routes->post('match-callups/(:num)/delete', 'Matches::deleteCallup/$1', ['filter' => ['csrf', 'permission:matches.update']]);

    $routes->post('matches/(:num)/lineup', 'Matches::saveLineup/$1', ['filter' => ['csrf', 'permission:match_lineup.manage']]);

    $routes->post('matches/(:num)/events', 'Matches::addEvent/$1', ['filter' => ['csrf', 'permission:match_stats.manage']]);
    $routes->post('match-events/(:num)/update', 'Matches::updateEvent/$1', ['filter' => ['csrf', 'permission:match_stats.manage']]);
    $routes->post('match-events/(:num)/delete', 'Matches::deleteEvent/$1', ['filter' => ['csrf', 'permission:match_stats.manage']]);

    $routes->post('matches/(:num)/report', 'Matches::saveReport/$1', ['filter' => ['csrf', 'permission:match_reports.manage']]);
    $routes->post('matches/(:num)/attachments', 'Matches::addAttachment/$1', ['filter' => ['csrf', 'permission:match_reports.manage']]);
    $routes->post('match-attachments/(:num)/delete', 'Matches::deleteAttachment/$1', ['filter' => ['csrf', 'permission:match_reports.manage']]);
});
