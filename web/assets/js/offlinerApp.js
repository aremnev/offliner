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
    $scope.message = '';
    $scope.stat ={'queue':0,'progress':0,'done':0};
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
    $scope.connect = function(prov){
        console.log('hello'+prov);
    }
    $scope.inEdit = function(task){
        return ($scope.inEditId == task.id && task.status == 'in queue');
    }
    $scope.setEditId = function(id){
        $scope.inEditId = id;
    }
    $scope.saveEdit = function(task){
        $scope.sendRequest('updateTask',task.id,{url:task.url,onlyDomain:task.onlyDomain,clearScripts:task.clearScripts,maxDepth:task.maxDepth});
        $scope.setEditId(-1);
    }
    $scope.sendRequest = function(req_action,opt_id,opt_data){
        var action;
        var method;
        var data = {};
        switch (req_action){
            case 'getTasks':
                action = 'tasks';
                method = 'POST';
                break;
            case 'newTask':
                action = 'tasks/new';
                method = 'POST';
                data = {url:$scope.url,onlyDomain:$scope.onlyDomain,clearScripts:$scope.clearScripts,maxDepth:$scope.maxDepth};
                break;
            case 'updateTask':
                action = 'tasks/new';
                method = 'PUT';
                data = {id:opt_id,url:opt_data.url,onlyDomain:opt_data.onlyDomain,clearScripts:opt_data.clearScripts,maxDepth:opt_data.maxDepth};
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
                        $scope.stat ={'queue':data['queue'],'progress':data.progress,'done':data.done};
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
                    default:
                }
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
    $scope.sendRequest('getStat');
    $scope.sendRequest('getTasks');

});

