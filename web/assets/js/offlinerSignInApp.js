'use strict';

var app = angular.module("offlinerSignInApp",['ui.bootstrap']);

app.controller("mainCtrl",function($scope,$http,$window){
   $scope.stat ={'queue':0,'progress':0,'done':0};
    $scope.message = '';
   $scope.loading = false;

    $scope.email ='';
    $scope.nick ='';
    $scope.pass ='';

   $scope.sendStatRequest = function(){
        $http({method:'GET',url:'stat'})
            .success(function(data, status, headers, config) {
                $scope.stat ={'queue':data['queue'],'progress':data.progress,'done':data.done};
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
    $scope.sendStatRequest();
    $scope.sendLoginRequest = function(){
        $http({method:'GET',url:'login/'+$scope.email+'?pass='+$scope.pass})
            .success(function(data, status, headers, config) {
                if(data.code ==0){
                $scope.message = 'Successful login';
                window.location.reload(true);
                }else{
                    $scope.message = 'Login failed';
                }
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
    $scope.sendRegisterRequest = function(){
        $http({method:'GET',url:'register/'+$scope.email+'?pass='+$scope.pass+'&nick='+$scope.nick})
            .success(function(data, status, headers, config) {
                if(data.code ==0){
                    $scope.message = 'Successful register';
                    window.location.reload(true);
                }else{
                    $scope.message = 'Register failed';
                }
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
            });
    }
});

