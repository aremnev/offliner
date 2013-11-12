'use strict';

var app = angular.module("offlinerApp",[]);

app.controller("offlinerCtrl",function($scope,$http,$window){
    $scope.url = 'http://www.example.com';
    $scope.links = [];
    $scope.onlyDomain = true;
    $scope.clearScripts = false;
    $scope.maxDepth = 0;
    $scope.tasks = [];
    $scope.message = '';
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
                action = 'tasks/'+opt_id;
                method = 'PUT';
                data = {id:opt_id,url:opt_data.url,onlyDomain:opt_data.onlyDomain,clearScripts:opt_data.clearScripts,maxDepth:opt_data.maxDepth};
                break;
            case 'deleteTask':
                action = 'tasks/'+opt_id;
                method = "DELETE";
                break;
        }
        $http({method:method,url:action,data:data})
            .success(function(data, status, headers, config) {
                switch (req_action){
                    case 'getTasks':
                        if(data){
                            $scope.tasks = data;
                            for(var key in $scope.tasks){
                                var d1 = new Date($scope.tasks[key].date.date);
                                $scope.tasks[key].date = d1.setHours(d1.getHours() + ((new Date()).getTimezoneOffset() / 60 +13) ); //Europe/Berlin => +13
                            }
                        }else{
                            $scope.tasks = [];
                        }
                        break;
                    case 'newTask':
                        if(data){
                            $scope.message = 'Added';
                            $scope.sendRequest('getTasks');
                        }else{
                            $scope.message = 'Something wrong';
                        }
                        break;
                    case 'updateTask':
                        $scope.message = 'Updated';
                        break;
                    case 'deleteTask':
                        if(status == 204){
                            $scope.message = 'Deleted';
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
    $scope.sendRequest('getTasks');
});

