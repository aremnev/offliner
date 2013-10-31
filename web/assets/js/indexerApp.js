var app = angular.module("indexerApp",[]);
app.controller("indexerCtrl",function($scope,$http,$window){
    $scope.url = 'http://www.example.com';
    $scope.searchText = '';
    $scope.action = '';
    $scope.message = '';
    $scope.result = '';
    $scope.links = [];
    $scope.domains = [];
    $scope.showSMore = false;
    $scope.searchResult = '';
    $scope.showPagesIL = false;
    $scope.loading = false;
    $scope.showMessage=function(){
        return (!$scope.showPagesIL ? "Show page list":"Hide pages");
    }
    $scope.divArrow=function(div){
        return (!div ? '&#59236;':'&#59239;');
    }

    $scope.sendRequest = function(req_action,opt_id,opt_data){
        var action='';
        var data = '';
        var method = '';
        switch (req_action){
            case 'getDomains':
                action = 'domains';
                method = 'POST';
                break;
            case 'newDomain':
                action = 'domains/new';
                method = 'POST';
                data = {url:$scope.url};
                break;
            case 'updateDomain':
                action = 'domains/'+opt_id;
                method = 'PUT';
                data = {id:opt_id,url:opt_data.url};
                break;
            case 'deleteDomain':
                action = 'domains/'+opt_id;
                method = "DELETE";
                break;
        }
        $http({method:method,url:action,data:data})
            .success(function(data, status, headers, config) {
                switch (req_action){
                    case 'getDomains':
                        if(data){
                            $scope.domains = data;
                        }else{
                            $scope.domains = [];
                        }
                        break;
                    case 'newDomain':
                        if(data){
                            $scope.message = 'Added';
                            $scope.sendRequest('getDomains');
                        }else{
                            $scope.message = 'Something wrong';
                        }
                        break;
                    case 'updateDomain':
                        $scope.message = 'Updated';
                        break;
                    case 'deleteDomain':
                        if(data){
                            $scope.message = 'Deleted';
                            $scope.sendRequest('getDomains');
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

    $scope.sendSearchRequest = function(){
        $scope.links = [];
        $scope.loading = true;
        $http({method:"POST",url:'search',data:{text:$scope.searchText}})
            .success(function(data, status, headers, config) {
                if(data.length > 0){
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
    $scope.sendRequest('getDomains');
});

