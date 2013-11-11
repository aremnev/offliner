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
                method = 'GET';
                break;
            case 'getDomainInfo':
                action = 'domains/'+opt_id;
                method = 'GET';
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
                    case 'getDomainInfo':
                        if(data){
                            $scope.result = $scope.domainTemplate(data,opt_data);
                        }else{
                            $scope.result = '<h4>'+opt_data.url+' statistics is empty</h4>';
                        }
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
                        if(status == 204){
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

    $scope.domainTemplate = function(data,domain){
        var result ='';
        result += '<div class="page-info">';
        result += '<h4>'+domain.url+':</h4>';
        result += '<div class="stats">';
        result += '<span class="stats-item"><span class="stats-label">In queue</span> <span class="stats-count">' + data.await + '</span></span>';
        result += '<span class="stats-item"><span class="stats-label">In progress</span> <span class="stats-count">' + data.progress + '</span></span>';
        result += '<span class="stats-item"><span class="stats-label">Done</span>  <span class="stats-count">' + data.ready + '</span></span>';
        result += '</div>';
        if(data.lastTotal){
            proc = Math.round(data.ready/data.lastTotal*100);
            if(proc != 100){
                result += '<h4>Refreshing index: '+proc+'%</h4>';
            }else{
                var now = new Date();
                var d = new Date(domain.refreshDate.date);
                now.setHours(now.getHours() + ((new Date()).getTimezoneOffset() / 60 +1));
                console.log(now);
                console.log(d);
                console.log(now-d);
                console.log(parseInt(24-(now-d)/(3600*1000)));
                result += '<h4>Index is actual. Until next refreshing: '+parseInt(24-(now-d)/(3600*1000))+' hours</h4>'
            }
        }else{
            result += '<h4>Initial indexing</h4>';
        }
        result += '</div>';
       /* console.log(data)
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
        result += '</div>';*/
        return result;
    }
    $scope.sendRequest('getDomains');
});

