'use strict';

var app = angular.module("editApp",['ui.bootstrap','miscEdit','ui.validate'])

app.controller("sectionsCtrl",function($scope,$http,$dialog,$translate){
    $scope.alerts = [];
    $scope.basicCollapsed = false;
    $scope.clps={'title': true}
    $scope.uncollapsed = 'basic';
    $scope.collapsed = true;
    $scope.uploadClps = true;
    $scope.upload = '';
    $scope.cv ={};


    $scope.selectSect = function(sect){
        if(sect != 'basic'){
            if(!sect.data){
                $http({method:"GET",url:"sect/"+sect.id})
                    .success(function(data, status, headers, config) {
                        sect.data = data;
                        $scope.uncollapsed = sect.id;
                    }).error(function(data, status, headers, config) {
                        $scope.data = {message:status};
                    });
            }else{
                $scope.uncollapsed = sect.id;
            }
        }else{
            $scope.uncollapsed = 'basic';
        }
    }
    $scope.isCollapsed = function(sect_id){
        if($scope.uncollapsed == sect_id){return false;}else{return true;}
    }

    $http({method:"GET",url:"get"})
            .success(function(data, status, headers, config) {
                $scope.cv = data;
            }).error(function(data, status, headers, config) {
                $scope.data = {message:status};
            });

    $scope.saveCv = function(){
        $http({method:"PUT",url:"update", params:{json: angular.toJson($scope.cv) }})
            .success(function(data, status, headers, config) {
                $scope.addAlert("Saved",'success');
            }).error(function(data, status, headers, config) {
                $scope.data = {message:status};
            });
    }

    $scope.addSection = function(){
        $http({method:"POST",url:"sect/new"})
            .success(function(data, status, headers, config) {
                $scope.cv.sections.push(data);
                $scope.addAlert("New section created",'success');
            }).error(function(data, status, headers, config) {
                $scope.data = {message:status};
            });
    }

    $scope.delSection = function(sect){
        $http({method:"DELETE",url:"sect/"+sect.id})
            .success(function(data, status, headers, config) {
                $scope.cv.sections.splice($scope.cv.sections.indexOf(sect),1);
                $scope.addAlert("Section deleted",'success');
            }).error(function(data, status, headers, config) {
                $scope.data = {message:status};
            });
    }

    $scope.addAlert = function(msg,type) {
        $scope.alerts.push({type: type ,msg: msg});
    };

    $scope.closeAlert = function(index) {
        $scope.alerts.splice(index, 1);
    };
    $scope.changeLanguage = function (langKey) {
        $translate.uses(langKey);
    };
});
app.directive("basicInfo",function(){
    return{
        restrict: "E",
        replace:true,
        templateUrl: 'basic_angular'
    }
})
app.directive("customInfo",function(){
    return{
        restrict: "E",
        replace:true,
        template:"<div class=\"row-fluid\" ng-repeat=\"sect in cv.sections\" collapse =\"isCollapsed(sect.id)\">"+
                     "<h4>{{sect.title}}</h4>"+
                     "<textarea ck-editor style=\"width: 400px; height: 100px;\" ng-model=\"sect.data\"></textarea>"+
                 "</div>"
    }
});
