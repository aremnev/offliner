var app = angular.module("indexerApp",[]);
app.controller("indexerCtrl",function($scope,$http,$window){
    $scope.url = 'http://www.example.com';
    $scope.searchText = '';
    $scope.action = 'checkStatus';
    $scope.message = '';
    $scope.result = '';
    $scope.links = [];
    $scope.displayAdd = false;
    $scope.displayDomain = false;
    $scope.stat ={'query':0,'progress':0,'done':0};
    $scope.showSMore = false;
    $scope.searchResult = '';
    $scope.showPagesIL = false;
    $scope.loading = false;
    $scope.getActionName=function(action){
        if(!action){
            action = $scope.action;
        }
        switch (action){
            case 'toQuery':
                return 'Add to indexing queue';
                break;
            case 'checkStatus':
                return 'Check page status';
                break;
            case 'domainInfo':
                return 'Check domain info';
                break;
            default :
                return 'Unknown action';
        }
    }
    $scope.showMessage=function(){
        return (!$scope.showPagesIL ? "Show page list":"Hide pages");
    }
    $scope.divArrow=function(div){
        return (!div ? '&#59236;':'&#59239;');
    }

    $scope.sendRequest = function(opt_act){
        var action = 'check';
        $scope.loading = true;
        if(opt_act){
            $scope.action = opt_act;
        }
        switch ($scope.action){
            case 'toQuery':
                action = 'toquery';
                break;
            case 'checkStatus':
                action = 'check';
                break;
            case 'domainInfo':
                action = 'domaininfo';
                break;
        }
        $http({method:"POST",url:action,data:{url:$scope.url}})
            .success(function(data, status, headers, config) {
                switch ($scope.action){
                    case 'toQuery':
                        if(data){
                            $scope.message += '<p><h5>Page:'+$scope.url+' successfuly added to queue</h5>';
                            $scope.displayAdd = false;
                            $scope.displayDomain = true;
                            $scope.sendRequest('getStat');
                        }else{
                            $scope.message += '<p><h5>Page:'+$scope.url+' already in database.</h5>';
                            $scope.displayAdd = false;
                            $scope.displayDomain = true;
                        }
                        break;
                    case 'checkStatus':
                        $scope.result =$scope.checkTemplate(data);
                        if($scope.displayAdd == false)
                            $scope.displayDomain = true;
                        break;
                    case 'domainInfo':
                        $scope.result =$scope.domainTemplate(data);
                        break;
                }
                $scope.loading = false;
            }).error(function(data, status, headers, config) {
                $scope.message = 'Response failed! Status:'+status;
                $scope.loading = false;
            });
    }

    $scope.sendSearchRequest = function(){
        $scope.links = [];
        $scope.loading = true;
        $http({method:"POST",url:'search',data:{text:$scope.searchText}})
            .success(function(data, status, headers, config) {
                if(data.length > 1){
                    $scope.links = data;
                    $scope.searchResult = '';
                }else{
                    $scope.searchResult = 'Sorry...No matches';
                    $scope.links = [];
                }
                $scope.loading = false;
            }).error(function(data, status, headers, config) {
                $scope.message = status;
                $scope.loading = false;
            });
    }

    $scope.checkTemplate = function(data){
        var result ='';
        if(status == 'Not in database'){
            result += '<p>Page status: '+status+'</p>';
            result += '<p>You can add this page to indexing queue</p>'
            $scope.displayAdd = true;
        }else{
            result += '<div class="page-info">';

            if(status=='Ready'){
                result += '<p>'
                    + '<a href="' + $scope.url + '">' + data.title + '</a> '
                    + '<a class="label label-info" href="/preview?url=' + data.hash_url + '" target="_blank">'
                    + '<small> Saved copy</small></a>'
                    + '</p>';
            }
            result += '<p><span class="icon icon-wf">&#127758;</span> ' + data.domain + '</p>';
            result += '<p>indexed: '+data.date+' </p>';
            result += '<p>Page status: '+data.status+'</p>';
            result += '</div>';
        }
        return result;
    }

    $scope.domainTemplate = function(data){
        var status;
        var result ='';
        result += '<div class="page-info">';
        result += '<h4>'+data.domain+' stat:</h4>';
        result += '<div class="stats">';
        result += '<span class="stats-item"><span class="stats-label">In queue</span> <span class="stats-count">' + data.query + '</span></span>';
        result += '<span class="stats-item"><span class="stats-label">In progress</span> <span class="stats-count">' + data.progress + '</span></span>';
        result += '<span class="stats-item"><span class="stats-label">Done</span>  <span class="stats-count">' + data.done + '</span></span>';
        result += '</div>';
        result += '</div>';
        console.log(data)
        if(data.pages.length > 0){
            result += '<p><a ng-click="showPagesIL = !showPagesIL">{{showMessage();}}</a></p>';
        }

        result += '<div ng-if="showPagesIL"  ng-animate="\'item-list\'" class="pages-item-list">';
        for(var key in data.pages){
            data.pages[key].url = decodeURIComponent(data.pages[key].url);
            result += '<div class="pages-item">'
                + '<span class="status">' + data.pages[key].status + '</span>'
                + '<div class="pages-item-title"><a href="' + data.pages[key].url + '" target="_blank">' + (data.pages[key].title!='unavailable' ? data.pages[key].title:"") + '</a></div>'
                + '<div class="pages-item-url">' + (data.pages[key].url.length < 90 ? data.pages[key].url:data.pages[key].url.substr(0,90)+'...') + '</div>'
                + '<div class="pages-item-copy"><a href="/preview?url=' + data.pages[key].hash_url + '" target="_blank">'+(data.pages[key].title!='unavailable' ? "Saved copy":"")+'</a></div>'
                +'</div>';
        }
        result += '</div>';
        return result;
    }

});

