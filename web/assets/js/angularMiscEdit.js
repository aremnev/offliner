'use strict';

var app = angular.module("miscEdit",['ui.bootstrap','angularFileUpload','pascalprecht.translate'])

app.controller("dialogCtrl",function($scope,$http,$dialog){


    var t = '<div class="modal-header">'+
        '<h3>Rename Section</h3>'+
        '</div>'+
        '<div class="modal-body">'+
        '<p>Enter new section name: <input ng-model="result" /></p>'+
        '</div>'+
        '<div class="modal-footer">'+
        '<button ng-click="close(result)" class="btn btn-primary" >Rename</button>'+
        '</div>';

    $scope.opts = {
        backdrop: true,
        keyboard: true,
        backdropClick: true,
        template:  t, // OR: templateUrl: 'path/to/view.html',
        controller: 'DialogController'
    };
    $scope.renameDialog = function(sect){
        var d = $dialog.dialog($scope.opts);
        d.open().then(function(result){
            if(result)
            {
                sect.title = result;
            }
        });
    };
    $scope.shareDialog = function(){
        var title = 'Share that url for your friends';
        var msg = 'http://localhost'+$scope.cv.shortlink;
        var btns = [{result:'mail', label: 'Send by e-mail'}, {result:'ok', label: 'OK', cssClass: 'btn-primary'}];

        $dialog.messageBox(title, msg, btns)
            .open()
            .then(function(result){
                if(result == 'mail'){
                    var mailmsg = {link:$scope.cv.shortlink, to: $scope.cv.basic.email};
                    $http({method:"GET",url:"sendmail",params:{json: angular.toJson(mailmsg)}})
                        .success(function(data, status, headers, config) {
                            $scope.addAlert("Email sended to "+$scope.cv.basic.email,'success');
                        }).error(function(data, status, headers, config) {
                            $scope.data = {message:status};
                        });
                }
            });
    };

});
app.directive('ckEditor', function() {
    return {
        require: '?ngModel',
        link: function(scope, elm, attr, ngModel) {
            var ck = CKEDITOR.replace(elm[0]);

            if (!ngModel) return;

            ck.on('pasteState', function() {
                scope.$apply(function() {
                    ngModel.$setViewValue(ck.getData());
                });
            });

            ngModel.$render = function(value) {
                ck.setData(ngModel.$viewValue);
            };
        }
    };
});
// the dialog is injected in the specified controller
function DialogController($scope, dialog){
    $scope.close = function(result){
        dialog.close(result);
    };
};
app.controller('uploadCtrl', function ($scope, $fileUploader) {
    // create a uploader with options
    var uploader = $fileUploader.create({
        scope: $scope,                          // to automatically update the html. Default: $rootScope
        url: 'upload',
        filters: [
            function (item) {                    // first user filter
                if(item.type=='image/jpeg')
                    return true;
                return false;
            }
        ]
    });

    // ADDING FILTER

    // REGISTER HANDLERS

    uploader.bind('afteraddingfile', function (event, item) {
        $scope.upload = 'ready';
    });
    uploader.bind('beforeupload', function (event, item) {
        $scope.cv.basic.photo = 'loading....';
    });
    uploader.bind('success', function (event, xhr, item) {
        $scope.upload = '';
        $scope.cv.basic.photo = xhr.response;
        $sope.apply();
    });

    $scope.uploader = uploader;
});
app.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.translations('en', {
        'FULLNAME': 'Fullname',
        'PHONE': 'Phone',
        'ADDRESS': 'Address',
        'WEBSITE': 'Web site',
        'PHOTO': 'Photo',
        'EXPORT': 'Export as:',
        'EMAIL': 'EMAIL',
        'DROPZONE':'Image Drop Zone',
        'EDIT_BASIC':'Edit basic information',
        'UPLOAD': 'Upload',
        'EDIT': 'Edit',
        'RENAME': 'Rename',
        'ADD_SECT':'Add section',
        'DELETE': 'Delete',
        'BASIC': 'Base Information'
    });

    $translateProvider.translations('ru', {
        'FULLNAME': 'Полное имя',
        'PHONE': 'Телефон',
        'ADDRESS': 'Аддресс',
        'EXPORT': 'Сохранить как:',
        'WEBSITE': 'Сайт',
        'PHOTO': 'Фотограффия',
        'EMAIL': 'Электронная почта',
        'DROPZONE':'Перетащите файл с фотографией сюда',
        'UPLOAD': 'Загрузить',
        'EDIT_BASIC':'Редактировать основную информацию',
        'EDIT': 'Редактировать',
        'RENAME': 'Переименовать',
        'ADD_SECT':'Добавить секцию',
        'DELETE': 'Удалить',
        'BASIC': 'Основная информация'
    });

    $translateProvider.preferredLanguage('ru');
}]);