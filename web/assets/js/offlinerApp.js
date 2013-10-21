'use strict';

var app = angular.module("offlinerApp",['ui.bootstrap'], function($compileProvider){
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

app.controller("mainCtrl",function($scope,$http,$window){
    $scope.url = 'http://katoart.ru';
    $scope.onlyDomain = true;
    $scope.clearScripts = false;
    $scope.maxDepth = 0;
    $scope.tasks = [];
    $scope.message = '<p>Manage your tasks</p>';
    $scope.stat ={'query':0,'progress':0,'done':0};
    $scope.showForm = false;
    $scope.inEditId = -1;
    $scope.divArrow=function(div){
        return (!div ? '&#59236;':'&#59239;');
    }
    $scope.validate = function(){
        if($scope.maxDepth > 5){
            $scope.maxDepth = 5
        }
        if($scope.maxDepth <0){
            $scope.maxDepth = 0;
        }
    }
    $scope.inEdit = function(task){
        console.log($scope.inEditId);
        return ($scope.inEditId == task.id && task.status == 'in query');
    }
    $scope.setEditId = function(id){
        $scope.inEditId = id;
    }
    $scope.sendRequest = function(req_action,opt_id){
        var action;
        var method;
        var data = {};
        switch (req_action){
            case 'getTasks':
                action = 'tasks';
                method = 'GET';
                break;
            case 'newTask':
                action = 'tasks/new';
                method = 'POST';
                data = {url:$scope.url,onlyDomain:$scope.onlyDomain,clearScripts:$scope.clearScripts,maxDepth:$scope.maxDepth};
                break;
            case 'getStat':
                action = 'stat';
                method = 'GET';
                break;
            case 'deleteTask':
                action = 'tasks/'+opt_id;
                method = "DELETE";
                break;
            default:
                action = 'stat';
                method = "GET";
        }
        $http({method:method,url:action,data:data})
            .success(function(data, status, headers, config) {
                switch (req_action){
                    case 'getTasks':
                        if(data){
                            $scope.tasks = [];
                            for(var key in data){
                                $scope.tasks.push(JSON.parse(data[key]));
                            }
                        }else{
                            $scope.tasks = [];
                        }
                        break;
                    case 'newTask':
                        if(data){
                            $scope.message = 'Added';
                            $scope.sendRequest('getStat');
                            $scope.sendRequest('getTasks');
                        }else{
                            $scope.message = 'Something wrong';
                        }
                        break;
                    case 'getStat':
                        $scope.stat ={'query':data.query,'progress':data.progress,'done':data.done};
                        break;
                    case 'deleteTask':
                        if(data){
                            $scope.message = 'Deletion successful';
                            $scope.sendRequest('getStat');
                            $scope.sendRequest('getTasks');
                        }else{
                            $scope.message = 'Something wrong';
                        }
                        break;
                }
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
    $scope.sendRequest('getStat');
    $scope.sendRequest('getTasks');
});

