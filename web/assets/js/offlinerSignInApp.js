'use strict';

var app = angular.module("offlinerSignInApp",['ui.bootstrap']);
//TODO: validation
app.controller("mainCtrl",function($scope,$http,$window){
    $scope.message = '';
   $scope.loading = false;

    $scope.email ='';
    $scope.nick ='';
    $scope.pass ='';
    $scope.sendLoginRequest = function(){
        $http({method:'GET',url:'login/'+$scope.email+'?pass='+$scope.pass})
            .success(function(data, status, headers, config) {
                if(data.code ==0){
                $scope.message = 'Successful login';
                window.location.reload(true);
                }
            }).error(function(data, status, headers, config) {
                if(data.code == 1){
                    $scope.message = 'Login failed';
                }else{
                $scope.message = 'Response failed! Status:'+status;
                }
            });
    }
    $scope.sendRegisterRequest = function(){
        $http({method:'GET',url:'register/'+$scope.email+'?pass='+$scope.pass+'&nick='+$scope.nick})
            .success(function(data, status, headers, config) {
                if(data.code ==0){
                    $scope.message = 'Successful register';
                    window.location.reload(true);
                }
            }).error(function(data, status, headers, config) {
                if(data.code == 1){
                    $scope.message = 'Register failed';
                }else{
                    $scope.message = 'Response failed! Status:'+status;
                }
            });
    }
});

