'use strict';

var app = angular.module("mainApp",['ui.bootstrap','offlinerApp','indexerApp','ngCookies'], function($compileProvider){
    // configure new 'compile' directive by passing a directive
    // factory function. The factory function injects the '$compile'
    $compileProvider.directive('compile', function($compile) {
        // directive factory creates a link function
        return function(scope, element, attrs) {
            scope.$watch(
                function(scope) {
                    // watch the 'compile' expression for changes
                    return scope.$eval(attrs.compile);
                },
                function(value) {
                    // when the 'compile' expression changes
                    // assign it into the current DOM
                    element.html(value);
                    // compile the new DOM and link it to the current
                    // scope.
                    // NOTE: we only compile .childNodes so that
                    // we don't get into infinite loop compiling ourselves
                    $compile(element.contents())(scope);
                }
            );
        };
    })
});

app.controller("mainCtrl",function($rootScope,$scope,$http,$window){
    $scope.message = '';
    $scope.stat ={'queue':0,'progress':0,'done':0};
    $scope.sendRequest = function(){
        $http({method:'GET',url:'stat'})
            .success(function(data, status, headers, config) {
               $scope.stat ={'queue':data['queue'],'progress':data.progress,'done':data.done};
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
    $scope.sendRequest();
    $rootScope.ready = true;
});
app.controller("tabsCtrl",function($rootScope,$cookies){
    $rootScope.tabSelect = function(tabname){
        $cookies.service = tabname;
    }
    $rootScope.tabs = {'profile':false,'offliner':true,'search':false,'indexer':false};
    if($rootScope.tabs.hasOwnProperty($cookies.service)){
        $rootScope.tabs[$cookies.service] = true;
    }
});