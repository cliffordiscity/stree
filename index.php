<html>

<?php

    $t_parent_id = 'com';
    $t_bns_id = date('Ym');

//    $t_parent_id = 'IDEXPRESS1';
//    $t_bns_id = '201505';

    if(isset($_POST['t_parent_id'])){
        $t_parent_id = $_POST['t_parent_id'];
    }

    if(isset($_POST['t_bns_id'])){
        $t_bns_id = $_POST['t_bns_id'];
    }

?>

<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.12/angular.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-router/0.2.14/angular-ui-router.js"></script>

<div ng-app="app" ui-view style="margin-left: -20px;  margin-right: 30px;  margin-top: 10px;">
</div>

<script type="application/javascript">


var app = angular.module('app', ['ui.router']).constant('t_parent_id','<?php echo $t_parent_id; ?>').constant('t_bns_id','<?php echo $t_bns_id; ?>');


app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.defaults.useXDomain = true;
    delete $httpProvider.defaults.headers.common['X-Requested-With'];
}
]);

app.config(function($stateProvider, $urlRouterProvider) {

    $urlRouterProvider.otherwise('/');
    $stateProvider
     .state('/', {
                url: '/',
                templateUrl: 'dirParttb.html',
                controller : 'AppCtrl',
                resolve : {
                    formBuilder : function(HttpServices,t_parent_id,t_bns_id){
                        return HttpServices.get('http://mbns.iscity.com.my/upt_indo/custom/c_ajax_tree_bns_b.php?t_parent_id='+t_parent_id+'&t_bns_id='+t_bns_id+'&type=top');
                    }
                }

            });
    });

app.controller('AppCtrl', function ($scope,HttpServices,formBuilder,t_bns_id) {

    $scope.nodes = [];
    $scope.title = "";
    $scope.col_defs = [];
    $scope.err = false;


    var initData = formBuilder.data;
    $scope.title = formBuilder.title;
    $scope.col_defs = formBuilder.col_defs

    $scope.admission = function(rawData,fidx){
        for (i = 0; i < rawData.length; i++) {
            if(fidx === undefined ){
                rawData[i]['fidx'] = String(i);
            }else{
                rawData[i]['fidx'] = String(fidx)+String(i);
            }
            rawData[i]['expand'] = false;
            rawData[i]['subnodes'] = [];
        }
        return rawData;
    };

    $scope.kidnapper = function(item){
        HttpServices.get('http://mbns.iscity.com.my/upt_indo/custom/c_ajax_tree_bns_b.php?t_parent_id='+item.id+'&t_bns_id='+t_bns_id)
                .then(function(response) {
                    victim = response.data;
                        item.subnodes = $scope.admission(victim,item.fidx);
                });
    };


    $scope.shide = function (item) {
        if(item.subnodes.length == 0){
            $scope.kidnapper(item);
            item.expand = true;
        }else{
            if(item.expand){
                item.expand = false;
            }else{
                item.expand = true;
            }

        }


    };

    $scope.objectKeys = function(obj){
        return Object.keys(obj);
    };

    if(formBuilder.err_id == undefined){
        var parent = angular.copy([initData[0]]);
        $scope.nodes = $scope.admission(parent);
        initData.splice(0, 1);
        $scope.nodes[0].subnodes = $scope.admission(initData,'0');
        $scope.nodes[0].expand = true;
    }else{
        $scope.err = true;
        $scope.err_msg = formBuilder.err_msg;
    }


});

app.factory('HttpServices', function ($http, $q) {
    return {
        get : function(r_url,params) {
            return  $http.get(r_url)
                    .then(function(response) {
                        if (typeof response.data === 'object') {
                            return response.data;
                        } else {
                            // invalid response
                            return $q.reject(response.data);
                        }
                    }, function(response) {
                        // something went wrong
                        return $q.reject(response.data);
                    });
        },
        post : function(r_url,params){
            return $http.post(r_url,params);
        }
    };
});

</script>
</html>