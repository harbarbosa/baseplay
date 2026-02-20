<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        \App\Validation\TeamCategoryRules::class,
    ];

    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    public array $login = [
        'email'    => 'required|valid_email',
        'password' => 'required|min_length[6]',
    ];

    public array $login_errors = [
        'email' => [
            'required'    => 'Informe o e-mail.',
            'valid_email' => 'E-mail invÃ¡lido.',
        ],
        'password' => [
            'required'   => 'Informe a senha.',
            'min_length' => 'A senha deve ter pelo menos 6 caracteres.',
        ],
    ];

    public array $passwordRequest = [
        'email' => 'required|valid_email',
    ];

    public array $passwordRequest_errors = [
        'email' => [
            'required'    => 'Informe o e-mail.',
            'valid_email' => 'E-mail invÃ¡lido.',
        ],
    ];

    public array $passwordReset = [
        'token'    => 'required',
        'password' => 'required|min_length[6]',
    ];

    public array $passwordReset_errors = [
        'token' => [
            'required' => 'Token invÃ¡lido.',
        ],
        'password' => [
            'required'   => 'Informe a nova senha.',
            'min_length' => 'A senha deve ter pelo menos 6 caracteres.',
        ],
    ];

    public array $userCreate = [
        'name'     => 'required|min_length[3]',
        'email'    => 'required|valid_email|is_unique[users.email]',
        'password' => 'required|min_length[6]',
        'role_id'  => 'required|integer',
    ];

    public array $roleCreate = [
        'name' => 'required|min_length[3]|is_unique[roles.name]',
    ];

    public array $teamCreate = [
        'name'        => 'required|min_length[3]|max_length[120]|teamNameUnique',
        'short_name'  => 'permit_empty|max_length[50]',
        'description' => 'permit_empty',
        'status'      => 'permit_empty|in_list[active,inactive]',
    ];

    public array $teamCreate_errors = [
        'name' => [
            'required'   => 'Informe o nome da equipe.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
            'max_length' => 'O nome deve ter no mÃ¡ximo 120 caracteres.',
            'is_unique'  => 'JÃ¡ existe uma equipe com esse nome.',
        ],
    ];

    public array $teamUpdate = [
        'name'        => 'required|min_length[3]|max_length[120]|teamNameUnique[{id}]',
        'short_name'  => 'permit_empty|max_length[50]',
        'description' => 'permit_empty',
        'status'      => 'permit_empty|in_list[active,inactive]',
    ];

    public array $categoryCreate = [
        'name'          => 'required|max_length[80]|categoryNameUnique[{team_id}]',
        'year_from'     => 'permit_empty|integer',
        'year_to'       => 'permit_empty|integer',
        'gender'        => 'permit_empty|in_list[mixed,male,female]',
        'training_days' => 'permit_empty|max_length[100]',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $categoryCreate_errors = [
        'name' => [
            'required'   => 'Informe o nome da categoria.',
            'max_length' => 'O nome deve ter no mÃ¡ximo 80 caracteres.',
            'is_unique'  => 'JÃ¡ existe uma categoria com esse nome para esta equipe.',
        ],
        'gender' => [
            'in_list' => 'GÃªnero invÃ¡lido.',
        ],
        'status' => [
            'in_list' => 'Status invÃ¡lido.',
        ],
    ];

    public array $categoryUpdate = [
        'name'          => 'required|max_length[80]|categoryNameUnique[{team_id},{id}]',
        'year_from'     => 'permit_empty|integer',
        'year_to'       => 'permit_empty|integer',
        'gender'        => 'permit_empty|in_list[mixed,male,female]',
        'training_days' => 'permit_empty|max_length[100]',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $athleteCreate = [
        'category_id'   => 'required|integer|categoryIsActive',
        'first_name'    => 'required|min_length[2]|max_length[80]',
        'last_name'     => 'permit_empty|max_length[120]',
        'birth_date'    => 'required|valid_date[Y-m-d]',
        'document_id'   => 'permit_empty|max_length[30]',
        'position'      => 'permit_empty|max_length[30]',
        'dominant_foot' => 'permit_empty|in_list[right,left,both]',
        'height_cm'     => 'permit_empty|integer',
        'weight_kg'     => 'permit_empty|decimal',
        'medical_notes' => 'permit_empty',
        'internal_notes'=> 'permit_empty',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $athleteCreate_errors = [
        'category_id' => [
            'required' => 'Informe a categoria.',
            'integer'  => 'Categoria invÃ¡lida.',
            'categoryIsActive' => 'Categoria invÃ¡lida ou inativa.',
        ],
        'first_name' => [
            'required'   => 'Informe o nome.',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres.',
        ],
        'birth_date' => [
            'required'   => 'Informe a data de nascimento.',
            'valid_date' => 'Data de nascimento invÃ¡lida.',
        ],
        'dominant_foot' => [
            'in_list' => 'PÃ© dominante invÃ¡lido.',
        ],
    ];

    public array $athleteUpdate = [
        'category_id'   => 'required|integer|categoryIsActive',
        'first_name'    => 'required|min_length[2]|max_length[80]',
        'last_name'     => 'permit_empty|max_length[120]',
        'birth_date'    => 'required|valid_date[Y-m-d]',
        'document_id'   => 'permit_empty|max_length[30]',
        'position'      => 'permit_empty|max_length[30]',
        'dominant_foot' => 'permit_empty|in_list[right,left,both]',
        'height_cm'     => 'permit_empty|integer',
        'weight_kg'     => 'permit_empty|decimal',
        'medical_notes' => 'permit_empty',
        'internal_notes'=> 'permit_empty',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $guardianCreate = [
        'full_name'     => 'required|min_length[3]|max_length[150]',
        'phone'         => 'permit_empty|max_length[30]',
        'email'         => 'permit_empty|valid_email',
        'relation_type' => 'permit_empty|max_length[30]',
        'document_id'   => 'permit_empty|max_length[30]',
        'address'       => 'permit_empty',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $guardianCreate_errors = [
        'full_name' => [
            'required'   => 'Informe o nome completo.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.',
        ],
        'email' => [
            'valid_email' => 'E-mail invÃ¡lido.',
        ],
    ];

    public array $guardianUpdate = [
        'full_name'     => 'required|min_length[3]|max_length[150]',
        'phone'         => 'permit_empty|max_length[30]',
        'email'         => 'permit_empty|valid_email',
        'relation_type' => 'permit_empty|max_length[30]',
        'document_id'   => 'permit_empty|max_length[30]',
        'address'       => 'permit_empty',
        'status'        => 'permit_empty|in_list[active,inactive]',
    ];

    public array $athleteGuardianLink = [
        'athlete_id'  => 'required|integer|athleteExists',
        'guardian_id' => 'required|integer|guardianExists',
        'is_primary'  => 'permit_empty|in_list[0,1]',
        'notes'       => 'permit_empty|max_length[255]',
    ];

    public array $eventCreate = [
        'team_id'        => 'required|integer|teamExists',
        'category_id'    => 'required|integer|categoryExists',
        'type'           => 'required|in_list[TRAINING,MATCH,MEETING,EVALUATION,TRAVEL]',
        'title'          => 'required|min_length[3]|max_length[150]',
        'description'    => 'permit_empty',
        'start_datetime' => 'required|valid_date[Y-m-d H:i:s]',
        'end_datetime'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'location'       => 'permit_empty|max_length[190]',
        'status'         => 'permit_empty|in_list[scheduled,cancelled,completed]',
    ];

    public array $eventCreate_errors = [
        'team_id' => [
            'required' => 'Informe a equipe.',
            'teamExists' => 'Equipe invÃ¡lida.',
        ],
        'category_id' => [
            'required' => 'Informe a categoria.',
            'categoryExists' => 'Categoria invÃ¡lida.',
        ],
        'type' => [
            'required' => 'Informe o tipo do evento.',
        ],
        'title' => [
            'required' => 'Informe o tÃ­tulo.',
            'min_length' => 'O tÃ­tulo deve ter pelo menos 3 caracteres.',
        ],
        'start_datetime' => [
            'required' => 'Informe a data e hora de inÃ­cio.',
            'valid_date' => 'Data/hora invÃ¡lida.',
        ],
    ];

    public array $eventUpdate = [
        'team_id'        => 'required|integer|teamExists',
        'category_id'    => 'required|integer|categoryExists',
        'type'           => 'required|in_list[TRAINING,MATCH,MEETING,EVALUATION,TRAVEL]',
        'title'          => 'required|min_length[3]|max_length[150]',
        'description'    => 'permit_empty',
        'start_datetime' => 'required|valid_date[Y-m-d H:i:s]',
        'end_datetime'   => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'location'       => 'permit_empty|max_length[190]',
        'status'         => 'permit_empty|in_list[scheduled,cancelled,completed]',
    ];

    public array $eventParticipantCreate = [
        'event_id'   => 'required|integer|eventExists',
        'athlete_id' => 'required|integer|athleteExists',
        'invitation_status' => 'permit_empty|in_list[invited,confirmed,declined,pending]',
        'notes' => 'permit_empty|max_length[255]',
    ];

    public array $attendanceCreate = [
        'event_id'   => 'required|integer|eventExists',
        'athlete_id' => 'required|integer|athleteExists',
        'status'     => 'required|in_list[present,late,absent,justified]',
        'notes'      => 'permit_empty|max_length[255]',
    ];

    public array $noticeCreate = [
        'team_id'     => 'permit_empty|integer|teamExists',
        'category_id' => 'permit_empty|integer|categoryExists',
        'title'       => 'required|min_length[3]|max_length[150]',
        'message'     => 'required|min_length[5]',
        'priority'    => 'permit_empty|in_list[normal,important,urgent]',
        'publish_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'expires_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'status'      => 'permit_empty|in_list[draft,published,archived]',
    ];

    public array $noticeCreate_errors = [
        'title' => [
            'required' => 'Informe o tÃ­tulo.',
            'min_length' => 'O tÃ­tulo deve ter pelo menos 3 caracteres.',
        ],
        'message' => [
            'required' => 'Informe a mensagem.',
            'min_length' => 'A mensagem deve ter pelo menos 5 caracteres.',
        ],
        'priority' => [
            'in_list' => 'Prioridade invÃ¡lida.',
        ],
        'publish_at' => [
            'valid_date' => 'Data/hora invÃ¡lida.',
        ],
        'expires_at' => [
            'valid_date' => 'Data/hora invÃ¡lida.',
        ],
        'status' => [
            'in_list' => 'Status invÃ¡lido.',
        ],
    ];

    public array $noticeUpdate = [
        'team_id'     => 'permit_empty|integer|teamExists',
        'category_id' => 'permit_empty|integer|categoryExists',
        'title'       => 'required|min_length[3]|max_length[150]',
        'message'     => 'required|min_length[5]',
        'priority'    => 'permit_empty|in_list[normal,important,urgent]',
        'publish_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'expires_at'  => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'status'      => 'permit_empty|in_list[draft,published,archived]',
    ];

    public array $documentTypeCreate = [
        'name' => 'required|min_length[2]|max_length[80]',
        'requires_expiration' => 'permit_empty|in_list[0,1]',
        'default_valid_days' => 'permit_empty|integer',
        'status' => 'permit_empty|in_list[active,inactive]',
    ];

    public array $documentTypeCreate_errors = [
        'name' => [
            'required' => 'Informe o nome do tipo.',
            'min_length' => 'O nome deve ter pelo menos 2 caracteres.',
        ],
        'default_valid_days' => [
            'integer' => 'Dias padrÃ£o invÃ¡lidos.',
        ],
    ];

    public array $documentCreate = [
        'document_type_id' => 'required|integer',
        'athlete_id' => 'permit_empty|integer|athleteExists',
        'guardian_id' => 'permit_empty|integer|guardianExists',
        'team_id' => 'permit_empty|integer|teamExists|documentOwnerRequired',
        'issued_at' => 'permit_empty|valid_date[Y-m-d]',
        'expires_at' => 'permit_empty|valid_date[Y-m-d]',
        'status' => 'permit_empty|in_list[active,archived,expired]',
        'document_file' => 'uploaded[document_file]|max_size[document_file,10240]|ext_in[document_file,pdf,jpg,jpeg,png]|mime_in[document_file,application/pdf,image/jpg,image/jpeg,image/png]',
    ];

    public array $documentUpdate = [
        'document_type_id' => 'required|integer',
        'athlete_id' => 'permit_empty|integer|athleteExists',
        'guardian_id' => 'permit_empty|integer|guardianExists',
        'team_id' => 'permit_empty|integer|teamExists|documentOwnerRequired',
        'issued_at' => 'permit_empty|valid_date[Y-m-d]',
        'expires_at' => 'permit_empty|valid_date[Y-m-d]',
        'status' => 'permit_empty|in_list[active,archived,expired]',
        'document_file' => 'permit_empty|max_size[document_file,10240]|ext_in[document_file,pdf,jpg,jpeg,png]|mime_in[document_file,application/pdf,image/jpg,image/jpeg,image/png]',
    ];

    public array $documentCreate_errors = [
        'document_type_id' => [
            'required' => 'Informe o tipo de documento.',
        ],
        'team_id' => [
            'documentOwnerRequired' => 'Informe um atleta, responsavel ou equipe.',
        ],
        'document_file' => [
            'uploaded' => 'Informe um arquivo.',
            'max_size' => 'Arquivo muito grande (mÃ¡x. 10MB).',
            'ext_in' => 'Formato invÃ¡lido. Use PDF, JPG ou PNG.',
            'mime_in' => 'Formato invÃ¡lido. Use PDF, JPG ou PNG.',
        ],
        'expires_at' => [
            'valid_date' => 'Data de vencimento invÃ¡lida.',
        ],
    ];

    public array $exerciseCreate = [
        'title' => 'required|min_length[3]|max_length[150]',
        'duration_min' => 'permit_empty|integer',
        'players_min' => 'permit_empty|integer|playersRangeValid',
        'players_max' => 'permit_empty|integer|playersRangeValid',
        'age_group' => 'permit_empty|in_list[u10,u11,u12,u13,u14,u15,u16,u17,u18,u19,u20,all]',
        'intensity' => 'permit_empty|in_list[low,medium,high]',
        'status' => 'permit_empty|in_list[active,inactive]',
    ];

    public array $exerciseCreate_errors = [
        'title' => [
            'required' => 'Informe o tÃ­tulo.',
            'min_length' => 'O tÃ­tulo deve ter pelo menos 3 caracteres.',
        ],
        'players_min' => [
            'playersRangeValid' => 'Jogadores mÃ­nimo nÃ£o pode ser maior que o mÃ¡ximo.',
        ],
        'players_max' => [
            'playersRangeValid' => 'Jogadores mÃ­nimo nÃ£o pode ser maior que o mÃ¡ximo.',
        ],
    ];

    public array $trainingPlanCreate = [
        'team_id' => 'required|integer|teamExists',
        'category_id' => 'required|integer|categoryExists',
        'title' => 'required|min_length[3]|max_length[150]',
        'status' => 'permit_empty|in_list[draft,ready,archived]',
    ];

    public array $trainingPlanCreate_errors = [
        'team_id' => [
            'required' => 'Informe a equipe.',
        ],
        'category_id' => [
            'required' => 'Informe a categoria.',
        ],
        'title' => [
            'required' => 'Informe o tÃ­tulo.',
        ],
    ];

    public array $trainingPlanBlockCreate = [
        'training_plan_id' => 'required|integer',
        'block_type' => 'permit_empty|in_list[warmup,technical,tactical,physical,small_sided,match,other]',
        'title' => 'required|min_length[2]|max_length[150]',
        'duration_min' => 'required|integer',
        'order_index' => 'required|integer',
        'media_url' => 'permit_empty|valid_url_strict',
    ];

    public array $trainingPlanBlockCreate_errors = [
        'title' => [
            'required' => 'Informe o tÃ­tulo do bloco.',
        ],
        'duration_min' => [
            'required' => 'Informe a duraÃ§Ã£o.',
        ],
        'order_index' => [
            'required' => 'Informe a ordem.',
        ],
        'media_url' => [
            'valid_url_strict' => 'Informe uma URL vÃ¡lida para vÃ­deo.',
        ],
    ];

    public array $trainingSessionCreate = [
        'team_id' => 'required|integer|teamExists',
        'category_id' => 'required|integer|categoryExists',
        'title' => 'required|min_length[3]|max_length[150]',
        'session_date' => 'required|valid_date[Y-m-d]',
    ];

    public array $trainingSessionCreate_errors = [
        'team_id' => [
            'required' => 'Informe a equipe.',
        ],
        'category_id' => [
            'required' => 'Informe a categoria.',
        ],
        'title' => [
            'required' => 'Informe o tÃ­tulo.',
        ],
        'session_date' => [
            'required' => 'Informe a data da sessÃ£o.',
        ],
    ];

    public array $trainingSessionAthleteCreate = [
        'training_session_id' => 'required|integer',
        'athlete_id' => 'required|integer|trainingSessionAthleteValid',
        'attendance_status' => 'permit_empty|in_list[present,late,absent,justified]',
        'rating' => 'permit_empty|integer|ratingRangeValid',
    ];

    public array $trainingSessionAthleteCreate_errors = [
        'athlete_id' => [
            'trainingSessionAthleteValid' => 'Não foi possível validar o atleta nesta sessão. Verifique atleta e sessão.',
        ],
        'rating' => [
            'ratingRangeValid' => 'Nota deve ser entre 1 e 10.',
        ],
    ];
    public array $matchCreate = [
        'team_id' => 'required|integer|teamExists',
        'category_id' => 'required|integer|categoryExists',
        'opponent_name' => 'required|min_length[3]|max_length[150]',
        'competition_name' => 'permit_empty|max_length[150]',
        'round_name' => 'permit_empty|max_length[80]',
        'match_date' => 'required|valid_date[Y-m-d]',
        'start_time' => 'permit_empty',
        'location' => 'permit_empty|max_length[190]',
        'home_away' => 'permit_empty|in_list[home,away,neutral]',
        'status' => 'permit_empty|in_list[scheduled,completed,cancelled]',
        'score_for' => 'permit_empty|integer',
        'score_against' => 'permit_empty|integer',
    ];

    public array $matchCreate_errors = [
        'team_id' => [
            'required' => 'Informe a equipe.',
        ],
        'category_id' => [
            'required' => 'Informe a categoria.',
        ],
        'opponent_name' => [
            'required' => 'Informe o adversÃ¡rio.',
            'min_length' => 'O nome do adversÃ¡rio deve ter pelo menos 3 caracteres.',
        ],
        'match_date' => [
            'required' => 'Informe a data do jogo.',
            'valid_date' => 'Data do jogo invÃ¡lida.',
        ],
    ];
}

