<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', 'Auth\\LoginController@showLoginForm')->name('default');

// @see vendor/laravel/ui/src/AuthRouteMethods.php
Auth::routes([
    'login' => false,
    'register' => false,
    'verify' => true,
]);

// auth
Route::get('signup', 'Auth\\RegisterController@index')->name('signup');
Route::post('signup', 'Auth\\RegisterController@store')->name('signup.attempt');
Route::get('login', 'Auth\\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\\LoginController@login')->name('login.attempt');

Route::get('invite/employee/{link}', 'Auth\\UserInvitationController@check');
Route::post('invite/employee/{link}/join', 'Auth\\UserInvitationController@join')->name('invitation.join');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('home', 'HomeController@index')->name('home');
    Route::post('search/employees', 'HeaderSearchController@employees');
    Route::post('search/teams', 'HeaderSearchController@teams');

    Route::post('help', 'HelpController@toggle');

    Route::resource('company', 'Company\\CompanyController')->only(['create', 'store']);

    // only available if user is in the right account
    Route::middleware(['company'])->prefix('{company}')->group(function () {
        Route::get('welcome', 'WelcomeController@index')->name('welcome');
        Route::post('hide', 'WelcomeController@hide');

        Route::get('notifications', 'User\\Notification\\NotificationController@index');
        Route::post('notifications/read', 'User\\Notification\\MarkNotificationAsReadController@store');

        // get the list of the pronouns in the company
        Route::get('pronouns', 'Company\\Company\\PronounController@index');

        // get the list of the positions in the company
        Route::get('positions', 'Company\\Company\\PositionController@index');

        Route::prefix('dashboard')->group(function () {
            Route::get('', 'Company\\Dashboard\\DashboardController@index')->name('dashboard');

            // me
            Route::get('me', 'Company\\Dashboard\\Me\\DashboardMeController@index')->name('dashboard.me');

            Route::post('worklog', 'Company\\Dashboard\\Me\\DashboardWorklogController@store');
            Route::post('morale', 'Company\\Dashboard\\Me\\DashboardMoraleController@store');
            Route::post('workFromHome', 'Company\\Dashboard\\Me\\DashboardWorkFromHomeController@store');
            Route::resource('question', 'Company\\Dashboard\\Me\\DashboardQuestionController')->only([
                'store', 'update', 'destroy',
            ]);
            Route::post('expense', 'Company\\Dashboard\\Me\\DashboardMeExpenseController@store')->name('dashboard.expense.store');

            // details of one on ones
            Route::get('oneonones/{entry}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@show')->name('dashboard.oneonones.show');
            Route::post('oneonones/{entry}/happened', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@markHappened');

            Route::post('oneonones/{entry}/talkingPoints', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@storeTalkingPoint');
            Route::post('oneonones/{entry}/talkingPoints/{talkingPoint}/toggle', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@toggleTalkingPoint');
            Route::post('oneonones/{entry}/talkingPoints/{talkingPoint}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@updateTalkingPoint');
            Route::delete('oneonones/{entry}/talkingPoints/{talkingPoint}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@destroyTalkingPoint');

            Route::post('oneonones/{entry}/actionItems', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@storeActionItem');
            Route::post('oneonones/{entry}/actionItems/{actionItem}/toggle', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@toggleActionItem');
            Route::post('oneonones/{entry}/actionItems/{actionItem}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@updateActionItem');
            Route::delete('oneonones/{entry}/actionItems/{actionItem}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@destroyActionItem');

            Route::post('oneonones/{entry}/notes', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@storeNote');
            Route::post('oneonones/{entry}/notes/{note}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@updateNote');
            Route::delete('oneonones/{entry}/notes/{note}', 'Company\\Dashboard\\Me\\DashboardMeOneOnOneController@destroyNote');

            // rate your manager
            Route::post('rate/{answer}', 'Company\\Dashboard\\Me\\DashboardRateYourManagerController@store');
            Route::post('rate/{answer}/comment', 'Company\\Dashboard\\Me\\DashboardRateYourManagerController@storeComment');

            // ecoffee
            Route::post('ecoffee/{ecoffee}/{match}', 'Company\\Dashboard\\Me\\DashboardMeECoffeeController@store');

            // timesheets
            Route::get('timesheet/projects', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController@projects')->name('dashboard.timesheet.projects');
            Route::get('timesheet/{timesheet}/projects/{project}/tasks', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController@tasks')->name('dashboard.timesheet.tasks');
            Route::resource('timesheet', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController', ['as' => 'dashboard'])->only([
                'index', 'show', 'destroy',
            ]);
            Route::post('timesheet/{timesheet}/store', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController@createTimeTrackingEntry')->name('dashboard.timesheet.entry.store');
            Route::post('timesheet/{timesheet}/submit', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController@submit')->name('dashboard.timesheet.entry.submit');
            Route::put('timesheet/{timesheet}/row', 'Company\\Dashboard\\Timesheets\\DashboardTimesheetController@destroyRow');

            // team
            Route::get('team', 'Company\\Dashboard\\Teams\\DashboardTeamController@index')->name('dashboard.team');
            Route::get('team/{team}', 'Company\\Dashboard\\Teams\\DashboardTeamController@index');
            Route::get('team/{team}/{date}', 'Company\\Dashboard\\Teams\\DashboardTeamController@worklogDetails');

            // manager tab
            Route::prefix('manager')->group(function () {
                Route::get('', 'Company\\Dashboard\\Manager\\DashboardManagerController@index')->name('dashboard.manager');
                Route::get('expenses/{expense}', 'Company\\Dashboard\\Manager\\DashboardManagerController@showExpense')->name('dashboard.manager.expense.show');
                Route::post('expenses/{expense}/accept', 'Company\\Dashboard\\Manager\\DashboardManagerController@accept');
                Route::post('expenses/{expense}/reject', 'Company\\Dashboard\\Manager\\DashboardManagerController@reject');

                // timesheets
                Route::get('timesheets', 'Company\\Dashboard\\Manager\\DashboardManagerTimesheetController@index')->name('dashboard.manager.timesheet.index');
                Route::get('timesheets/{timesheet}', 'Company\\Dashboard\\Manager\\DashboardManagerTimesheetController@show')->name('dashboard.manager.timesheet.show');
                Route::post('timesheets/{timesheet}/approve', 'Company\\Dashboard\\Manager\\DashboardManagerTimesheetController@approve');
                Route::post('timesheets/{timesheet}/reject', 'Company\\Dashboard\\Manager\\DashboardManagerTimesheetController@reject');
            });

            // hr tab
            Route::prefix('hr')->group(function () {
                Route::get('', 'Company\\Dashboard\\HR\\DashboardHRController@index')->name('dashboard.hr');

                // timesheets
                Route::get('timesheets', 'Company\\Dashboard\\HR\\DashboardHRTimesheetController@index')->name('dashboard.hr.timesheet.index');
                Route::get('timesheets/{timesheet}', 'Company\\Dashboard\\HR\\DashboardHRTimesheetController@show')->name('dashboard.hr.timesheet.show');
                Route::post('timesheets/{timesheet}/approve', 'Company\\Dashboard\\HR\\DashboardHRTimesheetController@approve');
                Route::post('timesheets/{timesheet}/reject', 'Company\\Dashboard\\HR\\DashboardHRTimesheetController@reject');
            });
        });

        Route::prefix('employees')->group(function () {
            Route::get('', 'Company\\Employee\\EmployeeController@index')->name('employees.index');

            // common to all pages
            Route::resource('{employee}/team', 'Company\\Employee\\EmployeeTeamController')->only([
                'index', 'store', 'destroy',
            ]);
            Route::resource('{employee}/position', 'Company\\Employee\\EmployeePositionController')->only([
                'store', 'destroy',
            ]);
            Route::resource('{employee}/employeestatuses', 'Company\\Employee\\EmployeeStatusController')->only([
                'index', 'store', 'destroy',
            ]);
            Route::resource('{employee}/pronoun', 'Company\\Employee\\EmployeePronounController')->only([
                'store', 'destroy',
            ]);
            Route::resource('{employee}/description', 'Company\\Employee\\EmployeeDescriptionController')->only([
                'store', 'destroy',
            ]);

            // Presentation tab
            Route::get('{employee}', 'Company\\Employee\\Presentation\\EmployeePresentationController@show')->name('employees.show');
            Route::put('{employee}/assignManager', 'Company\\Employee\\Presentation\\EmployeePresentationController@assignManager')->name('employee.manager.assign');
            Route::put('{employee}/assignDirectReport', 'Company\\Employee\\Presentation\\EmployeePresentationController@assignDirectReport')->name('employee.directReport.assign');
            Route::post('{employee}/search/hierarchy', 'Company\\Employee\\Presentation\\EmployeeSearchController@hierarchy');
            Route::put('{employee}/unassignManager', 'Company\\Employee\\Presentation\\EmployeePresentationController@unassignManager')->name('employee.manager.unassign');
            Route::put('{employee}/unassignDirectReport', 'Company\\Employee\\Presentation\\EmployeePresentationController@unassignDirectReport')->name('employee.directReport.unassign');
            Route::resource('{employee}/skills', 'Company\\Employee\\Presentation\\EmployeeSkillController')->only([
                'store', 'destroy',
            ]);
            Route::post('{employee}/skills/search', 'Company\\Employee\\Presentation\\EmployeeSkillController@search')->name('skills.search');
            Route::get('{employee}/ecoffees', 'Company\\Employee\\Presentation\\eCoffee\\EmployeeECoffeeController@index')->name('employees.ecoffees.index');

            // Edit page
            Route::put('{employee}/avatar/update', 'Company\\Employee\\Edit\\EmployeeEditAvatarController@update');
            Route::get('{employee}/edit', 'Company\\Employee\\Edit\\EmployeeEditController@show')->name('employee.show.edit');
            Route::get('{employee}/address/edit', 'Company\\Employee\\Edit\\EmployeeEditController@address')->name('employee.show.edit.address');
            Route::get('{employee}/contract/edit', 'Company\\Employee\\Edit\\EmployeeEditController@contract')->name('employee.show.edit.contract');
            Route::post('{employee}/contract/update', 'Company\\Employee\\Edit\\EmployeeEditController@updateContractInformation');
            Route::post('{employee}/update', 'Company\\Employee\\Edit\\EmployeeEditController@update');
            Route::post('{employee}/address/update', 'Company\\Employee\\Edit\\EmployeeEditController@updateAddress');
            Route::post('{employee}/rate/store', 'Company\\Employee\\Edit\\EmployeeEditController@storeRate');
            Route::delete('{employee}/rate/{rate}', 'Company\\Employee\\Edit\\EmployeeEditController@destroyRate');

            Route::get('{employee}/logs', 'Company\\Employee\\EmployeeLogsController@index')->name('employee.show.logs');

            // administration tab
            Route::prefix('{employee}/administration')->group(function () {
                Route::middleware(['employeeOrManagerOrAtLeastHR'])->group(function () {
                    Route::get('', 'Company\\Employee\\Administration\\EmployeeAdministrationController@show')->name('employees.administration.show');

                    // expenses
                    Route::resource('expenses', 'Company\\Employee\\Administration\\Expenses\\EmployeeExpenseController', ['as' => 'employee.administration'])->only([
                        'index', 'show',
                    ]);

                    // timesheets
                    Route::get('timesheets', 'Company\\Employee\\Administration\\Timesheets\\EmployeeTimesheetController@index')->name('employee.timesheets.index');
                    Route::get('timesheets/{timesheet}', 'Company\\Employee\\Administration\\Timesheets\\EmployeeTimesheetController@show')->name('employee.timesheets.show');
                    Route::get('timesheets/overview/{year}', 'Company\\Employee\\Administration\\Timesheets\\EmployeeTimesheetController@year')->name('employee.timesheets.year');
                    Route::get('timesheets/overview/{year}/{month}', 'Company\\Employee\\Administration\\Timesheets\\EmployeeTimesheetController@month')->name('employee.timesheets.month');
                });
            });

            // work tab
            Route::prefix('{employee}/work')->group(function () {
                Route::get('', 'Company\\Employee\\Work\\EmployeeWorkController@show')->name('employees.show.work');

                // work from home
                Route::get('workfromhome', 'Company\\Employee\\Work\\WorkFromHome\\EmployeeWorkFromHomeController@index')->name('employee.work.workfromhome');
                Route::get('workfromhome/{year}', 'Company\\Employee\\Work\\WorkFromHome\\EmployeeWorkFromHomeController@year');
                Route::get('workfromhome/{year}/{month}', 'Company\\Employee\\Work\\WorkFromHome\\EmployeeWorkFromHomeController@month');

                // worklogs
                Route::get('worklogs/week/{week}/day/{day}', 'Company\\Employee\\Work\\EmployeeWorkController@worklogDay');
                Route::get('worklogs/week/{week}/day', 'Company\\Employee\\Work\\EmployeeWorkController@worklogDay');
            });

            // performance tab
            Route::prefix('{employee}/performance')->group(function () {
                Route::get('', 'Company\\Employee\\Performance\\EmployeePerformanceController@show')->name('employees.show.performance');

                // survey
                Route::get('surveys', 'Company\\Employee\\Performance\\Surveys\\EmployeeSurveysController@index')->name('employees.show.performance.survey.index');
                Route::get('/surveys/{survey}', 'Company\\Employee\\Performance\\Surveys\\EmployeeSurveysController@show')->name('employees.show.performance.survey.show');

                // one on ones
                Route::get('oneonones', 'Company\\Employee\\Performance\\OneOnOnes\\EmployeeOneOnOneController@index')->name('employees.show.performance.oneonones.index');
                Route::get('oneonones/{oneonone}', 'Company\\Employee\\Performance\\OneOnOnes\\EmployeeOneOnOneController@show')->name('employees.show.performance.oneonones.show');
            });
        });

        Route::prefix('teams')->group(function () {
            Route::get('', 'Company\\Team\\TeamController@index')->name('teams.index');
            Route::get('{team}', 'Company\\Team\\TeamController@show')->name('team.show');

            Route::post('{team}/members/search', 'Company\\Team\\TeamMembersController@index');
            Route::post('{team}/members/attach/{employee}', 'Company\\Team\\TeamMembersController@attach');
            Route::post('{team}/members/detach/{employee}', 'Company\\Team\\TeamMembersController@detach');

            Route::resource('{team}/description', 'Company\\Team\\TeamDescriptionController', ['as' => 'description'])->only([
                'store', 'destroy',
            ]);

            Route::resource('{team}/lead', 'Company\\Team\\TeamLeadController')->only([
                'store', 'destroy',
            ]);
            Route::post('{team}/lead/search', 'Company\\Team\\TeamLeadController@search');

            Route::resource('{team}/news', 'Company\\Team\\TeamNewsController');

            Route::resource('{team}/links', 'Company\\Team\\TeamUsefulLinkController')->only([
                'store', 'destroy',
            ]);

            Route::resource('{team}/ships', 'Company\\Team\\TeamRecentShipController');
            Route::post('{team}/ships/search', 'Company\\Team\\TeamRecentShipController@search');
        });

        Route::prefix('company')->group(function () {
            Route::get('', 'Company\\Company\\CompanyController@index');
            Route::post('guessEmployee/vote', 'Company\\Company\\CompanyController@vote');
            Route::get('guessEmployee/replay', 'Company\\Company\\CompanyController@replay');

            // Questions and answers
            Route::resource('questions', 'Company\\Company\\QuestionController', ['as' => 'company'])->only([
                'index', 'show',
            ]);
            Route::get('questions/{question}/teams/{team}', 'Company\\Company\\QuestionController@team');

            // Company news
            Route::resource('news', 'Company\\Company\\CompanyNewsController', ['as' => 'company'])->only([
                'index', 'show',
            ]);

            // Skills
            Route::get('skills', 'Company\\Company\\SkillController@index')->name('company.skills.index');
            Route::get('skills/{skill}', 'Company\\Company\\SkillController@show')->name('company.skills.show');
            Route::put('skills/{skill}', 'Company\\Company\\SkillController@update');
            Route::delete('skills/{skill}', 'Company\\Company\\SkillController@destroy');

            // Projects
            Route::prefix('projects')->group(function () {
                Route::get('', 'Company\\Company\\Project\\ProjectController@index');
                Route::get('create', 'Company\\Company\\Project\\ProjectController@create');
                Route::post('', 'Company\\Company\\Project\\ProjectController@store');
                Route::post('search', 'Company\\Company\\Project\\ProjectController@search');

                // project detail
                Route::get('{project}', 'Company\\Company\\Project\\ProjectController@show')->name('projects.show');
                Route::get('{project}/summary', 'Company\\Company\\Project\\ProjectController@show');

                Route::post('{project}/start', 'Company\\Company\\Project\\ProjectController@start');
                Route::post('{project}/pause', 'Company\\Company\\Project\\ProjectController@pause');
                Route::post('{project}/close', 'Company\\Company\\Project\\ProjectController@close');
                Route::post('{project}/lead/assign', 'Company\\Company\\Project\\ProjectController@assign');
                Route::post('{project}/lead/clear', 'Company\\Company\\Project\\ProjectController@clear');
                Route::get('{project}/edit', 'Company\\Company\\Project\\ProjectController@edit')->name('projects.edit');
                Route::post('{project}/description', 'Company\\Company\\Project\\ProjectController@description');
                Route::post('{project}/update', 'Company\\Company\\Project\\ProjectController@update');
                Route::get('{project}/delete', 'Company\\Company\\Project\\ProjectController@delete')->name('projects.delete');
                Route::delete('{project}', 'Company\\Company\\Project\\ProjectController@destroy');

                Route::post('{project}/links', 'Company\\Company\\Project\\ProjectController@createLink');
                Route::delete('{project}/links/{link}', 'Company\\Company\\Project\\ProjectController@destroyLink');

                Route::get('{project}/status', 'Company\\Company\\Project\\ProjectController@createStatus');
                Route::put('{project}/status', 'Company\\Company\\Project\\ProjectController@postStatus');

                // project decision logs
                Route::get('{project}/decisions', 'Company\\Company\\Project\\ProjectDecisionsController@index');
                Route::post('{project}/decisions/search', 'Company\\Company\\Project\\ProjectDecisionsController@search');
                Route::post('{project}/decisions/store', 'Company\\Company\\Project\\ProjectDecisionsController@store');
                Route::delete('{project}/decisions/{decision}', 'Company\\Company\\Project\\ProjectDecisionsController@destroy');

                // project members
                Route::get('{project}/members', 'Company\\Company\\Project\\ProjectMembersController@index');
                Route::post('{project}/members/search', 'Company\\Company\\Project\\ProjectMembersController@search');
                Route::post('{project}/members', 'Company\\Company\\Project\\ProjectMembersController@store');
                Route::delete('{project}/members/{member}', 'Company\\Company\\Project\\ProjectMembersController@destroy');

                // project messages
                Route::resource('{project}/messages', 'Company\\Company\\Project\\ProjectMessagesController', ['as' => 'projects']);

                // project tasks
                Route::resource('{project}/tasks', 'Company\\Company\\Project\\ProjectTasksController', ['as' => 'projects']);
                Route::put('{project}/tasks/{task}/toggle', 'Company\\Company\\Project\\ProjectTasksController@toggle');
                Route::post('{project}/tasks/lists/store', 'Company\\Company\\Project\\ProjectTaskListsController@store');
                Route::put('{project}/tasks/lists/{list}', 'Company\\Company\\Project\\ProjectTaskListsController@update');
                Route::delete('{project}/tasks/lists/{list}', 'Company\\Company\\Project\\ProjectTaskListsController@destroy');
                Route::get('{project}/tasks/{task}/timeTrackingEntries', 'Company\\Company\\Project\\ProjectTasksController@timeTrackingEntries');
                Route::post('{project}/tasks/{task}/log', 'Company\\Company\\Project\\ProjectTasksController@logTime');

                // files
                Route::get('{project}/files', 'Company\\Company\\Project\\ProjectFilesController@index');
                Route::post('{project}/files', 'Company\\Company\\Project\\ProjectFilesController@store');
                Route::delete('{project}/files/{file}', 'Company\\Company\\Project\\ProjectFilesController@destroy');
            });

            Route::prefix('groups')->group(function () {
                Route::get('', 'Company\\Company\\Group\\GroupController@index');
                Route::get('create', 'Company\\Company\\Group\\GroupController@create')->name('groups.new');
                Route::post('', 'Company\\Company\\Group\\GroupController@store');
                Route::post('search', 'Company\\Company\\Group\\GroupController@search');

                // group detail
                Route::get('{group}', 'Company\\Company\\Group\\GroupController@show')->name('groups.show');
                Route::get('{group}/edit', 'Company\\Company\\Group\\GroupController@edit')->name('groups.edit');
                Route::post('{group}/update', 'Company\\Company\\Group\\GroupController@update');
                Route::get('{group}/delete', 'Company\\Company\\Group\\GroupController@delete')->name('groups.delete');
                Route::delete('{group}', 'Company\\Company\\Group\\GroupController@destroy');

                // members
                Route::get('{group}/members', 'Company\\Company\\Group\\GroupMembersController@index')->name('groups.members.index');
                Route::post('{group}/members/search', 'Company\\Company\\Group\\GroupMembersController@search');
                Route::post('{group}/members/store', 'Company\\Company\\Group\\GroupMembersController@store');
                Route::post('{group}/members/remove', 'Company\\Company\\Group\\GroupMembersController@remove');

                // meetings
                Route::get('{group}/meetings', 'Company\\Company\\Group\\GroupMeetingsController@index')->name('groups.meetings.index');
                Route::get('{group}/meetings/create', 'Company\\Company\\Group\\GroupMeetingsController@create')->name('groups.meetings.new');
                Route::get('{group}/meetings/{meeting}', 'Company\\Company\\Group\\GroupMeetingsController@show')->name('groups.meetings.show');
                Route::delete('{group}/meetings/{meeting}', 'Company\\Company\\Group\\GroupMeetingsController@destroy');
                Route::post('{group}/meetings/{meeting}/toggle', 'Company\\Company\\Group\\GroupMeetingsController@toggleParticipant');
                Route::post('{group}/meetings/{meeting}/search', 'Company\\Company\\Group\\GroupMeetingsController@search');
                Route::post('{group}/meetings/{meeting}/add', 'Company\\Company\\Group\\GroupMeetingsController@addParticipant');
                Route::post('{group}/meetings/{meeting}/remove', 'Company\\Company\\Group\\GroupMeetingsController@removeParticipant');
                Route::post('{group}/meetings/{meeting}/setDate', 'Company\\Company\\Group\\GroupMeetingsController@setDate');
                Route::post('{group}/meetings/{meeting}/addAgendaItem', 'Company\\Company\\Group\\GroupMeetingsController@createAgendaItem');
                Route::post('{group}/meetings/{meeting}/updateAgendaItem/{agendaItem}', 'Company\\Company\\Group\\GroupMeetingsController@updateAgendaItem');
                Route::delete('{group}/meetings/{meeting}/agendaItem/{agendaItem}', 'Company\\Company\\Group\\GroupMeetingsController@destroyAgendaItem');
                Route::post('{group}/meetings/{meeting}/agendaItem/{agendaItem}/addDecision', 'Company\\Company\\Group\\GroupMeetingsController@createDecision');
                Route::delete('{group}/meetings/{meeting}/agendaItem/{agendaItem}/decisions/{meetingDecision}', 'Company\\Company\\Group\\GroupMeetingsController@destroyDecision');
                Route::get('{group}/meetings/{meeting}/presenters', 'Company\\Company\\Group\\GroupMeetingsController@getPresenters');
            });

            Route::prefix('hr')->group(function () {
                Route::get('', 'Company\\Company\\HR\\CompanyHRController@index');
            });
        });

        // only available to accountant role
        Route::middleware(['accountant'])->group(function () {
            Route::get('dashboard/expenses', 'Company\\Dashboard\\Accountant\\DashboardExpensesController@index');
            Route::get('dashboard/expenses/{expense}/summary', 'Company\\Dashboard\\Accountant\\DashboardExpensesController@summary')->name('dashboard.expenses.summary');
            Route::get('dashboard/expenses/{expense}', 'Company\\Dashboard\\Accountant\\DashboardExpensesController@show')->name('dashboard.expenses.show');
            Route::post('dashboard/expenses/{expense}/accept', 'Company\\Dashboard\\Accountant\\DashboardExpensesController@accept');
            Route::post('dashboard/expenses/{expense}/reject', 'Company\\Dashboard\\Accountant\\DashboardExpensesController@reject');
        });

        // only available to administrator role
        Route::middleware(['administrator'])->group(function () {
            Route::get('account/audit', 'Company\\Adminland\\AdminAuditController@index');

            Route::get('account/general', 'Company\\Adminland\\AdminGeneralController@index');
            Route::post('account/general/rename', 'Company\\Adminland\\AdminGeneralController@rename');
            Route::post('account/general/currency', 'Company\\Adminland\\AdminGeneralController@currency');
            Route::post('account/general/logo', 'Company\\Adminland\\AdminGeneralController@logo');
            Route::post('account/general/date', 'Company\\Adminland\\AdminGeneralController@date');

            Route::get('account/cancel', 'Company\\Adminland\\AdminCancelAccountController@index');
            Route::delete('account/cancel', 'Company\\Adminland\\AdminCancelAccountController@destroy');
        });

        // only available to hr role or administrator
        Route::middleware(['hr'])->group(function () {
            // adminland
            Route::get('account', 'Company\\Adminland\\AdminlandController@index');

            // employee list
            Route::get('account/employees', 'Company\\Adminland\\AdminEmployeeController@index')->name('account.employees.index');
            Route::get('account/employees/all', 'Company\\Adminland\\AdminEmployeeController@all')->name('account.employees.all');
            Route::get('account/employees/active', 'Company\\Adminland\\AdminEmployeeController@active')->name('account.employees.active');
            Route::get('account/employees/locked', 'Company\\Adminland\\AdminEmployeeController@locked')->name('account.employees.locked');
            Route::get('account/employees/noHiringDate', 'Company\\Adminland\\AdminEmployeeController@noHiringDate')->name('account.employees.no_hiring_date');

            //employee CRUD
            Route::get('account/employees/create', 'Company\\Adminland\\AdminEmployeeController@create')->name('account.employees.new');
            Route::get('account/employees/upload', 'Company\\Adminland\\AdminUploadEmployeeController@upload')->name('account.employees.upload');
            Route::get('account/employees/upload/archives', 'Company\\Adminland\\AdminUploadEmployeeController@index')->name('account.employees.upload.archive');
            Route::get('account/employees/upload/archives/{archive}', 'Company\\Adminland\\AdminUploadEmployeeController@show')->name('account.employees.upload.archive.show');
            Route::post('account/employees/upload/archives/{archive}/import', 'Company\\Adminland\\AdminUploadEmployeeController@import')->name('account.employees.upload.archive.import');
            Route::post('account/employees', 'Company\\Adminland\\AdminEmployeeController@store')->name('account.employees.create');
            Route::post('account/employees/storeUpload', 'Company\\Adminland\\AdminUploadEmployeeController@store');
            Route::get('account/employees/{employee}/delete', 'Company\\Adminland\\AdminEmployeeController@delete')->name('account.delete');
            Route::delete('account/employees/{employee}', 'Company\\Adminland\\AdminEmployeeController@destroy');
            Route::get('account/employees/{employee}/lock', 'Company\\Adminland\\AdminEmployeeController@lock')->name('account.lock');
            Route::post('account/employees/{employee}/lock', 'Company\\Adminland\\AdminEmployeeController@lockAccount');
            Route::get('account/employees/{employee}/unlock', 'Company\\Adminland\\AdminEmployeeController@unlock')->name('account.unlock');
            Route::post('account/employees/{employee}/unlock', 'Company\\Adminland\\AdminEmployeeController@unlockAccount');
            Route::get('account/employees/{employee}/permissions', 'Company\\Adminland\\AdminEmployeePermissionController@show')->name('account.employees.permission');
            Route::post('account/employees/{employee}/permissions', 'Company\\Adminland\\AdminEmployeePermissionController@store');
            Route::get('account/employees/{employee}/invite', 'Company\\Adminland\\AdminEmployeeController@invite')->name('account.employees.invite');
            Route::post('account/employees/{employee}/invite', 'Company\\Adminland\\AdminEmployeeController@sendInvite');

            // team management
            Route::resource('account/teams', 'Company\\Adminland\\AdminTeamController', ['as' => 'account_teams']);
            Route::get('account/teams/{team}/logs', 'Company\\Adminland\\AdminTeamController@logs');

            // position management
            Route::resource('account/positions', 'Company\\Adminland\\AdminPositionController');

            // flow management
            Route::resource('account/flows', 'Company\\Adminland\\AdminFlowController');

            // employee statuses
            Route::resource('account/employeestatuses', 'Company\\Adminland\\AdminEmployeeStatusController', ['as' => 'account_employeestatuses']);

            // company news
            Route::resource('account/news', 'Company\\Adminland\\AdminCompanyNewsController', ['as' => 'account_news']);

            // pto policies
            Route::resource('account/ptopolicies', 'Company\\Adminland\\AdminPTOPoliciesController');
            Route::get('account/ptopolicies/{ptopolicy}/getHolidays', 'Company\\Adminland\\AdminPTOPoliciesController@getHolidays');

            // questions
            Route::resource('account/questions', 'Company\\Adminland\\AdminQuestionController');
            Route::put('account/questions/{question}/activate', 'Company\\Adminland\\AdminQuestionController@activate')->name('questions.activate');
            Route::put('account/questions/{question}/deactivate', 'Company\\Adminland\\AdminQuestionController@deactivate')->name('questions.deactivate');

            // hardware
            Route::get('account/hardware/available', 'Company\\Adminland\\AdminHardwareController@available');
            Route::get('account/hardware/lent', 'Company\\Adminland\\AdminHardwareController@lent');
            Route::post('account/hardware/search', 'Company\\Adminland\\AdminHardwareController@search');
            Route::resource('account/hardware', 'Company\\Adminland\\AdminHardwareController');

            // expenses
            Route::resource('account/expenses', 'Company\\Adminland\\AdminExpenseController', ['as' => 'account'])->except(['show']);
            Route::post('account/expenses/search', 'Company\\Adminland\\AdminExpenseController@search');
            Route::post('account/expenses/employee', 'Company\\Adminland\\AdminExpenseController@addEmployee');
            Route::post('account/expenses/removeEmployee', 'Company\\Adminland\\AdminExpenseController@removeEmployee');

            // e-coffee
            Route::get('account/ecoffee', 'Company\\Adminland\\AdminECoffeeController@index');
            Route::post('account/ecoffee', 'Company\\Adminland\\AdminECoffeeController@store');
        });
    });
});
