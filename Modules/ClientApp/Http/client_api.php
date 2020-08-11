<?php

//Route::get('auth', 'Auth\LoginController@index');

Route::post('/auth', 'Auth\LoginController@NormalLogin');
Route::post('/auth/password/reset/request', "Auth\ForgotPasswordController@resetSendEmail");
Route::post('/auth/password/reset/', "Auth\ResetPasswordController@resetPass");

Route::group(['middleware' => ['auth.jwt']], function () {
    Route::get('/test123', "UserController@test123");
    Route::get('/', 'ClientAppController@index');
    Route::get("/auth/{id}", "UserController@authUser");
    Route::get("/getApps", "UserController@getApps");
    Route::get("/user/{id}/profile", "UserController@getProfile")->middleware('permissions:read|1');
    Route::post("/user/{id}/profile", "UserController@updateProfile")->middleware('permissions:update|1');
    Route::put("/user/{id}/credentials", "UserController@changeuserPassword")->middleware('permissions:update|1');
    Route::get("/user/{id}/getsectorg", "UserController@getsectorg")->middleware('permissions:read|1');

    /** User */
    Route::get("user", "UserController@index")->middleware('permissions:read|1');
    Route::get("/loadKpiSectorUsers/{sector}/{orgUnit}", "UserController@loadKpiSectorUsers");
    Route::get("/loadKpiOrgUsers/{orgUnit}/{sector}", "UserController@loadKpiOrgUsers");
    Route::get("/user/{id}", "UserController@show")->middleware('permissions:read|1');
    Route::post("user", "UserController@store")->middleware('permissions:create|1');
    Route::put("user/{id}", "UserController@update")->middleware('permissions:update|1');
    Route::delete("user/{id}", "UserController@destroy")->middleware('permissions:delete|1');
    Route::get("allGroup", "UserController@getGroups")->middleware('permissions:read|2');
    Route::get("allTenant", "UserController@getTenants")->middleware('permissions:read|11');
    /** Tenant */
    Route::get("tenant", "TenantController@index")->middleware('permissions:read|11');
    Route::get("/tenant/{id}", "TenantController@show")->middleware('permissions:read|11,dynamic');
    Route::post("tenant", "TenantController@store")->middleware('permissions:create|11');
    Route::put("tenant/{id}", "TenantController@update")->middleware('permissions:update|11');
    Route::delete("tenant/{id}", "TenantController@destroy")->middleware('permissions:delete|11');
    Route::get("tenantdata", "TenantController@tenantdata")->middleware('permissions:read|11');
    Route::get("tenantdataorg/{orgdata}", "TenantController@tenantdataorg")->middleware('permissions:read|11');
    Route::get("/mindmapdata/{id}/{linkflag}", "mindmapcontroller@mindmapdata")->middleware('permissions:read|11');
    Route::get("/loadSection/{id}", "mindmapcontroller@loadSection");
    /** Group */
    Route::get("group", "GroupController@index")->middleware('permissions:read|2');
    Route::get("/group/{id}", "GroupController@show")->middleware('permissions:read|2');
    Route::post("group", "GroupController@store")->middleware('permissions:create|2');
    Route::put("group/{id}", "GroupController@update")->middleware('permissions:update|2');
    Route::delete("group/{id}", "GroupController@destroy")->middleware('permissions:delete|2');
    /** Group Type */
    Route::get("groupType", "GroupTypeController@index")->middleware('permissions:read|2');
    Route::get("groupType/{id}", "GroupTypeController@show")->middleware('permissions:read|2');
    /** Task */
    Route::get("task", "UserController@index")->middleware('permissions:read|7');
    Route::get("/task/{id}", "UserController@show")->middleware('permissions:read|7');
    Route::post("task", "UserController@store")->middleware('permissions:create|7');
    Route::put("task/{id}", "UserController@update")->middleware('permissions:update|7');
    Route::delete("task/{id}", "UserController@destroy")->middleware('permissions:delete|7');
    /** Process */
    Route::get("process", "ProcessController@index")->middleware('permissions:read|6');
    Route::get("/process/{id}", "ProcessController@show")->middleware('permissions:read|6');
    Route::post("process", "ProcessController@store")->middleware('permissions:read|6');
    Route::put("process/{id}", "ProcessController@update")->middleware('permissions:read|6');
    Route::delete("process/{id}", "ProcessController@destroy")->middleware('permissions:read|6');
    Route::post('process/{id}/setting', 'ProcessController@setProcessSetting')->middleware('permissions:create|6');
    Route::get('process/{id}/setting', 'ProcessController@getProcessSetting')->middleware('permissions:create|6');
    Route::get('process/{id}/task', 'ProcessController@processTasks')->middleware('permissions:read|6');
    Route::post('process/{key}/instance', 'ProcessController@startProcessInstance')->middleware('permissions:read|6');

    Route::get('process-definition/{id}/xml', 'ProcessController@getProcessDefinationXML')->middleware('permissions:read|6');

    Route::get('process-definition/{id}/count', 'ProcessController@processDefinitionCount')->middleware('permissions:read|6');

    Route::get('process-definition/{id}/statistics', 'ProcessController@getProcessDefinationStatistics')->middleware('permissions:read|6');

    Route::get('process-instance-history/{id}', 'ProcessController@getHistoryProcessDefination')->middleware('permissions:read|6');

    Route::get('processinstance/{id}', 'ProcessController@getProcessInstanceDetails')->middleware('permissions:read|6');

    Route::get('singleprocessinstance/{id}', 'ProcessController@getSingleProcessInstance')->middleware('permissions:read|6');

    Route::get('process-instance/{id}/activity-instance', 'ProcessController@getProcessActiveInstance')->middleware('permissions:read|6');

    /** PRC Types */
    Route::get("PRCTypes", "PRCTypeController@index");
    Route::post("PRCTypes", "PRCTypeController@store");
    Route::get("PRCTypes/{id}", "PRCTypeController@show");
    Route::post("PRCTypes/{id}", "PRCTypeController@update");
    Route::delete("PRCTypes/{id}", "PRCTypeController@destroy");
    /** Priority Types */
    Route::get("PriorityType", "PriorityTypeController@index");
    Route::post("PriorityType", "PriorityTypeController@store");
    Route::get("PriorityType/{id}", "PriorityTypeController@show");
    Route::post("PriorityType/{id}", "PriorityTypeController@update");
    Route::delete("PriorityType/{id}", "PriorityTypeController@destroy");

    /** Permissions */
    Route::get("permission", "UserController@index")->middleware('permissions:read|0|permissions');
    Route::get("/permission/{id}", "UserController@show")->middleware('permissions:read|0|permissions');
    Route::post("permission", "UserController@store")->middleware('permissions:create|0|permissions');
    Route::put("permission/{id}", "UserController@update")->middleware('permissions:update|0|permissions');
    Route::delete("permission/{id}", "UserController@destroy")->middleware('permissions:delete|0|permissions');
    /** Vacation */
    Route::get("vacation", "UserController@index")->middleware('permissions:read|0');
    Route::get("/vacation/{id}", "UserController@show")->middleware('permissions:read|0');
    Route::post("vacation", "UserController@store")->middleware('permissions:create|0');
    Route::put("vacation/{id}", "UserController@update")->middleware('permissions:update|0');
    Route::delete("vacation/{id}", "UserController@destroy")->middleware('permissions:delete|0');

    Route::post('group_tasks', 'TaskController@groupTasks');
    Route::post('user_tasks', 'TaskController@userTasks');
    Route::post('user_task_details', 'TaskController@taskDetails');

    Route::post('claim_task', 'TaskController@cliamTask');
    Route::post('unclaim_task', 'TaskController@unClaimTask');
    Route::post('add_identity_link', 'TaskController@addIdentityLink');
    Route::post('remove_identity_link', 'TaskController@removeIdentityLink');

    Route::post('set_follow_up_date', 'TaskController@setFollowUpDate');
    Route::post('set_due_date', 'TaskController@setDueDate');
    Route::post('submit_task_form', 'TaskController@submitTaskForm');

    // Route::post('get_comment_list', 'TaskController@getCommentList');
    Route::post('create_comment_list', 'TaskController@createComment');

    /** Priority Types */
    Route::get("TaskTodos/{taskid}", "TaskTodosController@index");
    Route::post("TaskTodos", "TaskTodosController@store");
    Route::get("TaskTodos/{taskid}/{id}", "TaskTodosController@show");
    Route::put("TaskTodos/{id}", "TaskTodosController@update");
    Route::delete("TaskTodos/{todo_id}", "TaskTodosController@destroy");

    /** Roles */
    Route::get("roles", "RolesController@index");
    Route::get("getPermissions", "RolesController@getPermissions");
    Route::get("/roles/{id}", "RolesController@show");
    Route::post("roles", "RolesController@store");
    Route::put("roles/{id}", "RolesController@update");
    Route::delete("roles/{id}", "RolesController@destroy");
    Route::get("rolesObject", "RolesController@getRoleObject");
    Route::get("rolesList", "UserController@getRoleList");

    /** Roles */
    Route::get("object_models", "ObjectModelController@index");
    Route::get("/object_models/{id}", "ObjectModelController@show");
    Route::post("object_models", "ObjectModelController@store");
    Route::put("object_models/{id}", "ObjectModelController@update");
    Route::delete("object_models/{id}", "ObjectModelController@destroy");
    //Route::get("object_models" , "RolesController@getRoleList");


    /** Roles */
    Route::get("fg_form", "formGeneratorController@index");
    Route::get("fg_form/{id}/{kpi_id}", "formGeneratorController@getForm");
    Route::post("fg_form", "formGeneratorController@store");

    Route::get("/kpidefList", "formGeneratorController@kpidefList");
    Route::get("/kpidefListHistory/{start_date}/{end_date}", "formGeneratorController@kpidefListHistory");

    Route::get("/fetchCitiesForCountry/{id}", "formGeneratorController@fetchCitiesForCountry");
    Route::post("/fetchSectorChild/{id}", "formGeneratorController@fetchSectorChild");
    Route::get("/fetchUserData/{id}/{type}", "formGeneratorController@fetchUserData");
    Route::get("/fetchBenchMarkData/{id}", "formGeneratorController@fetchBenchMarkData");
    Route::post("/fetchProcessObject/{id}", "formGeneratorController@fetchProcessObject");

    Route::get("/fg_form_tabledata/{table}", "formGeneratorController@gettabledata");
    Route::get("/fg_form_tabledatabyName/{table}", "formGeneratorController@gettabledatabyName");
    Route::get("/tabledatabyId/{formId}/{id}", "formGeneratorController@tabledatabyId");
    Route::post("/tableeditadataid/{formId}/{dataid}/{data}", "formGeneratorController@tableeditadataid");
    Route::delete("tabledatadelete/{formId}/{id}", "formGeneratorController@destroy");

    Route::get("/loadTenants", "formGeneratorController@loadTenants");
    Route::get("/loadSubTenants/{id}", "formGeneratorController@loadSubTenants");
    Route::get("/loadCategory", "formGeneratorController@loadCategory");
    Route::get("/loadMtp", "formGeneratorController@loadMtp");
    Route::get("/loadfiscal/{mtpstart_date}/{mtpend_date}", "formGeneratorController@loadFiscal");
    Route::get("organizationchart", "organizationchart@index");

    Route::get("tables", "translationController@index");
    Route::get("gettablecolumns/{tablename}", "translationController@gettablecolumns");
    Route::post("Translations", "translationController@store");
    Route::get("loadtranslations", "translationController@loadtranslations");
    Route::get("translationdatabyId/{id}", "translationController@translationdatabyId");
    Route::get("gettranslations", "translationController@gettranslations");
    Route::delete("translationdatadelete/{id}", "translationController@destroy");

    Route::post("/kpiApproveReject", "formGeneratorController@kpiApproveReject");
    Route::post("/kpiActiveInactive", "formGeneratorController@kpiActiveInactive");
    Route::get("kpiexceptionlist/{id}", "formGeneratorController@kpiexceptionlist");
    Route::get("kpivalues/{id}", "formGeneratorController@kpivalues");
    Route::get("kpivalueshistory/{id}", "formGeneratorController@kpivalueshistory");
    Route::get("kpivaluesbyId/{id}", "formGeneratorController@kpivaluesbyId");
    Route::post("/kpivaluesUpdate", "formGeneratorController@kpivaluesUpdate");
    Route::post("/kpivalueshistorysave", "formGeneratorController@storehistory");
    Route::post("/kpibaseyvaluesave", "formGeneratorController@kpibaseyvaluesave");
    Route::get("/kpivaluetypechangecheck/{id}", "formGeneratorController@kpivaluetypechangecheck");
    Route::post("/kpivaluesdelete/{id}/{val}", "formGeneratorController@kpivaluesdelete");
    Route::delete("/removeKpiPermanently/{id}", "formGeneratorController@removeKpiPermanently");


    Route::get("gaugechart/{mtp_date}/{kpi_id}/{periodicity}", "GaugeController@index");
    Route::get("quartermap/{target_id}/{periodicity}/{valType}", "GaugeController@quarterMap");
    Route::get("mtp", "GaugeController@mtp");
    Route::get("loadkpi/{id}", "GaugeController@loadkpi");
    Route::get("loadkpisymbol/{id}", "GaugeController@loadkpisymbol");


    Route::get("mtpDashboard", "DashboardKPIController@mtp");
    Route::get("dashboardVal/{mtp_date}/{sector}", "DashboardKPIController@dashboardVal");
    Route::get("dashboardTableVal/{mtp_date}/{sector}", "DashboardKPIController@dashboardTableVal");
    Route::get("staticdashboardTableVal/{mtp_date}/{sector}/{getAll}", "DashboardKPIController@staticdashboardTableVal");
    //Route::get("/loadTenants" , "DashboardKPIController@loadTenants");
    //Route::get("/loadSubTenants/{id}" , "DashboardKPIController@loadSubTenants");
    Route::get("/loadKpiDataSector/{id}", "formGeneratorController@loadKpiDataSector");
    Route::get("/loadKpiDataOrgUnit/{id}", "formGeneratorController@loadKpiDataOrgUnit");
    Route::get("/getUserOfData/{id}", "formGeneratorController@getUserOfData");
    Route::get("/getUserOfAuditing/{id}", "formGeneratorController@getUserOfAuditing");

    Route::get("loginlogs", "loginlogController@index");
    Route::get("/logloadTenants", "loginlogController@loadTenants");
    Route::get("/logloadSubTenants/{id}", "loginlogController@loadSubTenants");
    Route::get("/loadloginlogDataSector/{id}", "loginlogController@loadloginlogDataSector");
    Route::get("/loadloginlogDataOrgUnit/{id}", "loginlogController@loadloginlogDataOrgUnit");
    Route::get("/loadloginDataDate/{id}", "loginlogController@loadloginDataDate");

    Route::get("audittrial", "audittrialController@index");
    Route::get("/logloadTenants", "audittrialController@loadTenants");
    Route::get("/logloadSubTenants/{id}", "audittrialController@loadSubTenants");
    Route::get("/loadusers", "audittrialController@loadusers");
    Route::get("/loadscreens", "audittrialController@loadscreens");
    Route::get("/loadaudittrialDataSector/{id}", "audittrialController@loadaudittrialDataSector");
    Route::get("/loadaudittrialDataOrgUnit/{id}", "audittrialController@loadaudittrialDataOrgUnit");
    Route::get("/loadaudittrialDataDate/{id}", "audittrialController@loadaudittrialDataDate");
    Route::get("/audittrialfilter/{value}", "audittrialController@audittrialfilter");

//process------------------------------------------------------------------------------------------------------------------------

    Route::get("processlistTableVal", "processlistController@index");

    Route::get("processlistTableValSector/{parent}", "processlistController@ProcessListTableSector");
    Route::get("processlistTableValOrg/{parent}", "processlistController@ProcessListTableOrg");

    Route::get("processlist1/{id}", "processlistController@show");
    Route::post("processlist1", "processlistController@store");
    Route::put("processlist1/{id}", "processlistController@update");
    Route::delete("processlist1/{id}", "processlistController@destroy");

    Route::get("linkedlistTableVal/{prc_id}", "processlistController@linkedlist");
    Route::post("linkedlist1", "processlistController@store1");
    Route::put("linkedlist1/{id}", "processlistController@update1");
    Route::delete("linkedlist1/{linkSector}/{linkOrg}", "processlistController@destroy1");

    Route::get("/prcloadTenants1", "processlistController@loadTenants");
    Route::get("/prcloadCategory1", "processlistController@loadCategory");
    Route::get("/prcloadSubTenants1/{id}", "processlistController@loadSubTenants");
    Route::get("/prcloadSubTenants11/{id}", "processlistController@loadlinkSubTenants");
    Route::get("/prcloadTenants11", "processlistController@loadlinkTenants");
//strategy---------------------------------------------------------------------------------------------------
Route::get("strategylistTableVal", "strategylistController@index");

Route::get("strategylistTableValSector/{parent}", "strategylistController@StrategyListTableSector");
Route::get("strategylistTableValOrg/{parent}", "strategylistController@StrategyListTableOrg");

Route::get("strategylist1/{id}", "strategylistController@show");
Route::post("strategylist1", "strategylistController@store");
Route::put("strategylist1/{id}", "strategylistController@update");
Route::delete("strategylist1/{id}", "strategylistController@destroy");

Route::get("strategylinkedlistTableVal/{prc_id}", "strategylistController@linkedlist");
Route::post("strategylinkedlist1", "strategylistController@store1");
Route::put("strategylinkedlist1/{id}", "strategylistController@update1");
Route::delete("strategylinkedlist1/{linkTenant}/{linkSector}/{linkOrg}/{linkId}", "strategylistController@destroy1");

Route::get("/stgloadTenants1", "strategylistController@loadTenants");
Route::get("/stgloadCategory1", "strategylistController@loadCategory");
Route::get("/stgloadSubTenants1/{id}", "strategylistController@loadSubTenants");
Route::get("/stgloadSubTenants11/{id}", "strategylistController@loadlinkSubTenants");
Route::get("/stgloadTenants11", "strategylistController@loadlinkTenants");
Route::get("/stgloadTenantsreal11", "strategylistController@loadlinkTenantsreal");


//holiday--------------------------------------------------------------------------------------------------------------

    Route::get("/scrape/{year}", "HolidayController@scrape");
    Route::get("/holiday", "HolidayController@index");
    Route::get("/holiday/{id}", "HolidayController@show");
    Route::post("/holiday", "HolidayController@store");
    Route::put("/holiday/{id}", "HolidayController@update");
    Route::delete("/holiday/{id}", "HolidayController@destroy");


    Route::get("/allholidays", "HolidayController@allholidays");
    Route::get("/years", "HolidayController@years");
    Route::get("/date/{id}/{year}", "HolidayController@date");

//--------------------------------------------------------------------------------------------------------------------

    Route::get("categorylistTableVal", "processcategoryController@index");
    Route::get("categorylist1/{id}", "processcategoryController@show");
    Route::post("categorylist1", "processcategoryController@store");
    Route::put("categorylist1/{id}", "processcategoryController@update");
    Route::delete("categorylist1/{id}", "processcategoryController@destroy");

    Route::get("perfprog", "performanceController@index");
    Route::get("/sectionkpireport/{value}", "performanceController@sectionkpireport");
    Route::get("/departmentreport/{value}", "performanceController@departmentreport");
    Route::get("/loadSubTenantsdept/{id}", "performanceController@loadSubTenants");
    Route::get("/getsubtenanttype/{value}", "performanceController@getsubtenanttype");


    Route::get("/ministrydepartmentreport/{value}", "ministryperformanceController@departmentreport");

    Route::get("/maintenanceData", "maintenanceController@loadMaintenance");
    Route::get("/maintenanceData/{id}/{mtp_id}", "maintenanceController@loadMaintenanceById");
    Route::get("/updatedb_kpivaluestates/{id}/{mtp_id}", "maintenanceController@updatedbKpivaluestates");

    Route::get("/subtenanttree/{id}", "treeselectcController@subtenanttree");




    Route::get("loadNotificationDefaultData1", "riskController@loadNotificationDefaultData");
    Route::get("/loadKpiOrgUsersNotification1/{orgUnit}/{sector}", "riskController@loadKpiOrgUsersNotification");
    Route::get("/risklist", "riskController@risklist");
    Route::get("/loaduserlist", "riskController@userlist");
    Route::get("/loadriskcatlist", "riskController@riskcatlist");
  
    Route::post("/riskstore", "riskController@store");
    Route::get("/projectlistfilter/{value}", "riskController@loadprojectearch");
    Route::get("risklist1/{id}", "riskController@show");
    Route::put("risklist1/{id}", "riskController@update");
    Route::delete("risklist1/{id}", "riskController@destroy");



});
Route::get("test", "HomeController@index");
Route::post("test", "HomeController@index");

Route::get("KpiStatusReport/{lang}/{sect}/{org}/{back}", "KpiStatusReportController@index");
Route::post("KpiStatusReport/{lang}/{sect}/{org}/{back}", "KpiStatusReportController@index");

Route::post("KpiPivotReport/{lang}/{sect}/{org}/{back}", "KpiPivotReportController@index");
Route::get("KpiPivotReport/{lang}/{sect}/{org}/{back}", "KpiPivotReportController@index");

Route::post("KpiExceptionReport/{lang}", "KpiExceptionReportController@index");
Route::get("KpiExceptionReport/{lang}", "KpiExceptionReportController@index");


// Route::get('/KpiStatusReport/{lang}/KpiValuesReport/{mtp_id}/{kpi_id}/{org_unit}/{kpi_symbol}/{kpi_name}', "KpiValuesReportController@statuslink");
// Route::get('/KpiStatusReport/{lang}/KpiValuesReport/{mtp_id}/{kpi_id}/{org_unit}', "KpiValuesReportController@statuslink");
Route::get('/KpiStatusReport/{lang}/{sect}/{org}/KpiValuesReport/{cluster}', "KpiValuesReportController@statuslink");

Route::post("KpiPerformanceReport/{lang}/{sect}/{org}/{back}" , "KpiPerformanceReportController@index");
Route::get("KpiPerformanceReport/{lang}/{sect}/{org}/{back}" , "KpiPerformanceReportController@index");

Route::post("UnitPerformanceReport/{lang}" , "UnitPerformanceReportController@index");
Route::get("UnitPerformanceReport/{lang}" , "UnitPerformanceReportController@index");

?>
