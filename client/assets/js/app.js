(function(angular) {
    'use strict';

    var app = angular.module('ConnectMangasApp', ['ngRoute', 'ngMaterial', 'ngCookies', 'sAlert', 'angularSpinner', 'ngSanitize', 'ngFileUpload', 'ngGeolocation', 'chat', 'infinite-scroll', 'ui.bootstrap', 'pubnub.angular.service']);
    const PATH_JG_HOME = "http://localhost/connectmangas/";
    const PATH_JG_TAF = "http://localhost/jg/test-fusion-connectmangas_v2/server/";
    const PATH_MAC = "http://localhost:8888/connectmangas/server/";
    const PATH_PROD = "http://connectmangas.com/server/";


    app.constant( 'config', {
        //
        // Get your PubNub API Keys in the link above.
        //
        "pubnub": {
            "publish-key"   : "pub-c-e33e9e87-84aa-417d-a31d-46a7c097b72d",
            "subscribe-key" : "sub-c-48476bca-c046-11e6-a856-0619f8945a4f"
        }
    } );

    /*
     * Gestion des routes
     * @param $routeProvider
     */
    app.config(function($routeProvider, $httpProvider) {

        $httpProvider.defaults.headers.post['Client-Service'] = 'frontend-client';
        $httpProvider.defaults.headers.post['Auth-Key'] = 'simplerestapi';

        $routeProvider.when('/', {
            templateUrl: 'client/pages/homepage.html',
            controller: 'HomeController'
        }).when('/manga/:mangaID', {
            templateUrl: 'client/pages/manga.html',
            controller: 'MangaController'
        }).when('/tomes/:mangaID', {
            templateUrl: 'client/pages/tomes.html',
            controller: 'TomesController'
        }).when('/anime/:animeID', {
            templateUrl: 'client/pages/anime.html',
            controller: 'AnimeController'
        }).when('/episodes/:animeID', {
            templateUrl: 'client/pages/episodes.html',
            controller: 'EpisodesController'
        }).when('/recherche/:searchParam', {
            templateUrl: 'client/pages/search.html',
            controller: 'SearchController'
        }).when('/authentification', {
            templateUrl: 'client/pages/authentication.html',
            controller: 'AuthenticationController',
            requireAuth: true
        }).when('/collection', {
            templateUrl: 'client/pages/collection.html',
            controller: 'CollectionController'
        }).when('/profil/:username', {
            templateUrl: 'client/pages/profile.html',
            controller: 'ProfileController',
            requireAuth: true
        }).when('/manga/:mangaID/tome/:tomeNumber', {
            templateUrl: 'client/pages/usersTome.html',
            controller: 'usersTomeController',
            requireAuth: true
        }).when('/calendrier', {
            templateUrl: 'client/pages/calendrier.html',
            controller: 'calendarController'
        }).when('/suivi', {
            templateUrl: 'client/pages/suivi.html',
            controller: 'suiviController'
        }).otherwise({
            redirectTo: '/'
        });

    });

    /*
     * Gestion des Factory
     * @param $http
     */
    app.factory('mangasService', function($http, $cookies, sAlert) {

        return {
            getMangaById: function(id) {

                if (angular.isUndefined($cookies.getObject('user'))){
                    var userID = 0;
                }else{
                    var userID = $cookies.getObject('user').userID;
                }

                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/manga/'+id,
                    headers: {
                        'User-ID': userID
                    }
                }).then(function(response) {

                    var manga = response.data.infos;
                    return manga;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            setMangaToCollection: function(id_manga, user) {
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/add_collection_manga',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_manga: id_manga
                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("Le manga a été ajouté à votre collection.").autoRemove();

                    var addMangaResult = response.data;
                    return addMangaResult;

                }, function errorCallback(response) {

                    if ( response.status == 403 && response.data.message == "Already in collection." )
                      sAlert.error("Le manga est déjà dans votre collection.").autoRemove();

                });
            },
            removeMangaFromCollection: function(id_manga, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_MAC+'api/action/delete_collection_manga',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_manga: id_manga
                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("Le manga a été retiré de votre collection.").autoRemove();

                    var removeMangaResult = response.data;
                    return removeMangaResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.message == "Not in collection." )
                    sAlert.error("Le manga n'est pas dans votre collection.").autoRemove();

                });
            }
        }

    });

    app.factory('tomesService', function($http, $cookies, sAlert) {

        return {
            getTomesById: function(id) {
                var userID = '';
                if (angular.isUndefined($cookies.getObject('user'))){
                    userID = 0;
                }else{
                    userID = $cookies.getObject('user').userID;
                }

                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/tomes/'+id,
                    headers: {
                        'User-ID': userID
                    }
                }).then(function(response) {

                    var tomes = response.data.infos;
                    return tomes;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            setTomeToCollection: function(id_manga, id_tome, user) {
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/add_collection_tome',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_manga: id_manga,
                        number: id_tome

                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("Le tome a été ajouté à votre collection.").autoRemove();

                    var addTomeResult = response.data;
                    return addTomeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error("Le tome est déjà dans votre collection.").autoRemove();

                  var addTomeResult = response.data;
                  return addTomeResult;

                });
            },
            removeTomeFromCollection: function(id_manga, id_tome, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_MAC+'api/action/delete_collection_tome',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_manga: id_manga,
                        number: id_tome

                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("Le tome a été retiré de votre collection.").autoRemove();

                    return response.data;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.message == "Not in collection." )
                    sAlert.error("Le tome n'est pas dans votre collection.").autoRemove();

                    return response.data;

                });
            }
        }

    });

    app.factory('animesService', function($http, $cookies, sAlert) {

        return {
            getAnimeById: function(id) {

                if (angular.isUndefined($cookies.getObject('user'))){
                    var userID = 0;
                }else{
                    var userID = $cookies.getObject('user').userID;
                }

                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/anime/'+id,
                    headers: {
                        'User-ID': userID
                    }
                }).then(function(response) {

                    var anime = response.data.infos;
                    return anime;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            setAnimeToCollection: function(id_anime, user) {
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/add_collection_anime',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_anime: id_anime
                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("L'anime a été ajouté à votre collection.").autoRemove();

                    var addAnimeResult = response.data;
                    return addAnimeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error("L'anime est déjà dans votre collection.").autoRemove();

                });
            },
            removeAnimeFromCollection: function(id_anime, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_MAC+'api/action/delete_collection_anime',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_anime: id_anime
                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("L'anime a été retiré de votre collection.").autoRemove();

                    var removeAnimeResult = response.data;
                    return removeAnimeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.message == "Not in collection." )
                    sAlert.error("L'anime n'est pas dans votre collection.").autoRemove();

                });
            }
        }

    });

    app.factory('episodesService', function($http, $cookies, sAlert) {

        return {
            getEpisodesById: function(id) {

                if (angular.isUndefined($cookies.getObject('user'))){
                    var userID = 0;
                }else{
                    var userID = $cookies.getObject('user').userID;
                }

                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/episodes/'+id,
                    headers: {
                        'User-ID': userID
                    }
                }).then(function(response) {

                    var episodes = response.data.infos;
                    return episodes;

                }, function errorCallback(response) {
                    console.log(response);
                });
            },
            setEpisodeToCollection: function(id_anime, id_episode, user) {
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/add_collection_episode',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_anime: id_anime,
                        number: id_episode
                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("L'épisode a été ajouté à votre collection.").autoRemove();

                    var addEpisodeResult = response.data;
                    return addEpisodeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error("L'épisode est déjà dans votre collection.").autoRemove();

                });
            },
            removeEpisodeFromCollection: function(id_anime, id_episode, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_MAC+'api/action/delete_collection_episode',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    data: {
                        id_anime: id_anime,
                        number: id_episode

                    }
                }).then(function(response) {

                    if ( response.status == 200 )
                      sAlert.success("L'épisode a été retiré de votre collection.").autoRemove();

                    var removeEpisodeResult = response.data;
                    return removeEpisodeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.message == "Not in collection." )
                    sAlert.error("L'épisode n'est pas dans votre collection.").autoRemove();

                });
            }
        }

    });

    app.factory('searchService', function($http) {

        return {
            getSearchResult: function(param) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/search/'+param,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi'
                    }
                }).then(function(response) {

                    var searchResult = response.data;
                    return searchResult;

                });
            }
        }

    });

    app.factory('authenticationService', function($http, sAlert, $location) {
        return {
            register: function(username, password, email){
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/register',
                    data: {username: username, password: password, email: email}
                }).success(function(){
                    sAlert.success("Compte enregistré avec succès.").autoRemove();
                    $location.path('/');
                }).error(function(data){
                    sAlert.error(data.message).autoRemove();
                });

            },
            login : function(username, password){
                return $http({
                    method: 'POST',
                    url: PATH_MAC+'api/action/login',
                    data: {username: username, password: password}
                }).success(function(data){
                    sAlert.success(data.message).autoRemove();
                    $location.path('/');
                }).error(function(data){
                    sAlert.error(data.message).autoRemove();
                });

            }
        };

    });

    app.factory('userService', function($http) {

        return {
            getUserById: function(id, token, username) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/profil/'+username,
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': token,
                        'User-ID': id
                    }
                }).then(function(response) {

                    var userResult = response.data;
                    return userResult;

                });
            }
        }

    });

    app.factory('collectionService', function($http) {

        return {
            getAllCollection: function(id, token) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/collection',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': token,
                        'User-ID': id
                    }
                }).then(function(response) {
                    return response.data;

                });
            },
            checkIfInCollection: function() {

            }
        }

    });

    app.factory('usersTomeService', function($http, $cookies) {

        return {
            getUsersByTome: function (id_manga, number, latitude, longitude) {
                var user = '';
                if (angular.isUndefined($cookies.getObject('user'))) {
                    user = 0;
                } else {
                    user = $cookies.getObject('user');
                }

                if (latitude && longitude){
                    var params = {
                        'id_manga': id_manga,
                        'number': number,
                        'latitude': latitude,
                        'longitude' : longitude
                    };
                }else{
                    var params = {
                        'id_manga': id_manga,
                        'number': number
                    };
                }

                return $http({
                    method: 'GET',
                    url: PATH_MAC + 'api/action/users_tome',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    params: params
                }).then(function (response) {
                    return response.data;

                }, function errorCallback(response) {

                    console.log(response);

                });
            }
        }
    });

    app.factory('calendarService', function($http) {

        return {
            getCalendar: function(type) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/calendar',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi'
                    },
                    params: {
                        type: type
                    }
                }).then(function(response) {
                    return response.data;
                });
            }
        }

    });

    app.factory('suiviService', function($http) {

        return {
            getSuivi: function(id, token) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC+'api/action/suivi',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': token,
                        'User-ID': id
                    }
                }).then(function(response) {
                    return response.data;
                });
            }
        }

    });

    /*
     * Gestion des controllers
     * @params $scope, $routeParams, factoryService
     */
    app.controller('AppCtrl', function($scope, $cookies, $location, $window, $rootScope, Messages, Pubnub, $pubnubChannel) {

        $scope.channel = 'messages-channel';
        // Generating a random uuid between 1 and 100 using an utility function from the lodash library.
        $scope.uuid = _.random(100).toString();
        Pubnub.init({
            publish_key   : "pub-c-e33e9e87-84aa-417d-a31d-46a7c097b72d",
            subscribe_key : "sub-c-48476bca-c046-11e6-a856-0619f8945a4f",
            uuid: $scope.uuid
        });

        $scope.sendMessage = function() {
            // Don't send an empty message
            var date = new Date();
            date = date.getHours()+":"+date.getMinutes();
            if (!$scope.textbox || $scope.textbox === '') {
                return;
            }
            if(typeof userCookie === "undefined") {
                var user = 'anonyme';
            } else {
                var user = userCookie.username;
            }
            Pubnub.publish({
                channel: $scope.channel,
                message: {
                    content: $scope.textbox,
                    sender_uuid: $scope.uuid,
                    user : user,
                    date: date
                },
                callback: function(m) {
                    console.log(m);
                }
            });
            // Reset the messageContent input
            $scope.textbox = '';
        };
        $scope.messages = $pubnubChannel($scope.channel, { autoload: 50 });
        //$scope.messages = [];

        // Subscribing to the ‘messages-channel’ and trigering the message callback
        Pubnub.subscribe({
            channel: $scope.channel,
            triggerEvents: ['callback']
        });

        // Listening to the callbacks
        /*$scope.$on(Pubnub.getMessageEventNameFor($scope.channel), function (ngEvent, m) {
            $scope.$apply(function () {
                console.log(m);
                $scope.messages.push(m);
                console.log($scope.messages);
            });
        });*/

        // A function to display a nice uniq robot avatar
        $scope.avatarUrl = function(uuid){
            return 'http://robohash.org/'+uuid+'?set=set2&bgset=bg2&size=70x70';
        };

        // DO SOMETHING
        var userCookie = $cookies.getObject('user');
        $rootScope.userCookie = userCookie;
        $scope.show = true;

        $scope.logout = function() {
            $cookies.remove('user');
            $window.location.reload();
            //$scope.$apply();
        };

        $scope.chat_hide = function() {
            $scope.show = false;
            var element = document.getElementById("qnimate");
            angular.element(element).addClass('chat-off');
            angular.element(element).removeClass('chat-on');
        };
        $scope.chat_show = function() {
            $scope.show = true;
            var element = document.getElementById("qnimate");
            angular.element(element).addClass('chat-on');
            angular.element(element).removeClass('chat-off');
        };


        $scope.searchFunc = function() {
            var search = escape($scope.mySearch.replace(/\//g,"_"));
            $location.path('/recherche/'+search);
        };

        //$scope.messages = [];

        /*$scope.loadMore = function() {
            if($scope.messages.length > 0) {
                var last = $scope.messages[$scope.messages.length - 1];
                for(var i = 1; i <= 8; i++) {
                    $scope.messages.push(last + i);
                }
            }
        };*/

        if(typeof userCookie === "undefined") {
            Messages.user({ id: '0' , name : 'anonyme'});
        } else {
            Messages.user({ id: userCookie.userID , name : userCookie.username});
        }
        // - - - - - - - - - - - - - - - - - -
        // Receive Messages
        // Push to Message Inbox.
        // - - - - - - - - - - - - - - - - - -
       /* Messages.receive(function(message){
            $scope.messages.push(message);
        });*/

        // - - - - - - - - - - - - - - - - - -
        // Send Message
        // This is a controller method used
        // when a user action occurs.
        // Also we expect a model reference
        // ng-model="textbox".
        // - - - - - - - - - - - - - - - - - -

        /*$scope.send = function() {
            Messages.send({ data : $scope.textbox});
            $scope.textbox = '';
        };*/

    });

    app.controller('HomeController', function($scope) {
        $scope.message = "This is the home page";
    });

    app.controller('MangaController', function($scope, $routeParams, $cookies, mangasService) {

        var promiseManga = mangasService.getMangaById($routeParams.mangaID);
        promiseManga.then(function(manga) {
            manga.synopsis = manga.synopsis;
            $scope.manga = manga;

            if ( manga.inCollection == '1' )
              $scope.isMangaInCollection = true;
            else
              $scope.isMangaInCollection = false;
        });

        var user = $cookies.getObject('user');

        $scope.addManga = function(id_manga) {
          var promiseAddManga = mangasService.setMangaToCollection(id_manga, user);
          promiseAddManga.then(function(response) {

              if ( response != undefined && response.status == 201 && response.message == "Data has been created." )
                $scope.isMangaInCollection = true;


          });
        };

        $scope.removeManga = function(id_manga) {
          var promiseRemoveManga = mangasService.removeMangaFromCollection(id_manga, user);
          promiseRemoveManga.then(function(response) {

              if ( response != undefined && response.status == 201 )
                  $scope.isMangaInCollection = false;

          });
        }

    });

    app.controller('TomesController', function($scope, $routeParams, $cookies, tomesService) {

        var promiseTomes = tomesService.getTomesById($routeParams.mangaID);
        promiseTomes.then(function(tomes) {
            $scope.tomes = tomes;
        });

        var user = $cookies.getObject('user');

        $scope.addTome = function(id_manga, id_tome, key) {
            var promiseAddTome = tomesService.setTomeToCollection(id_manga, id_tome, user);
            promiseAddTome.then(function(response) {
                if ( response != undefined && response.status == 201 )
                    $scope.tomes[key].inCollection = 1;

            });
        };

        $scope.removeTome = function(id_manga, id_tome, key) {
            var promiseRemoveTome = tomesService.removeTomeFromCollection(id_manga, id_tome, user);
            promiseRemoveTome.then(function(response) {
                if ( response != undefined && response.status == 201 )
                    $scope.tomes[key].inCollection = 0;

            });
        }

    });

    app.controller('AnimeController', function($scope, $routeParams, $cookies, animesService) {

        var promiseAnime = animesService.getAnimeById($routeParams.animeID);
        promiseAnime.then(function(anime) {
            anime.synopsis = anime.synopsis;
            $scope.anime = anime;
            if (anime.inCollection > 0){
                $scope.isAnimeInCollection = true;
            }else{
                $scope.isAnimeInCollection = false;
            }
        });

        var user = $cookies.getObject('user');

        $scope.addAnime = function(id_anime) {
            var promiseAddAnime = animesService.setAnimeToCollection(id_anime, user);
            promiseAddAnime.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isAnimeInCollection = true;

            });
        };

        $scope.removeAnime = function(id_anime) {
            var promiseRemoveAnime = animesService.removeAnimeFromCollection(id_anime, user);
            promiseRemoveAnime.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isAnimeInCollection = false;

            });
        }

    });

    app.controller('EpisodesController', function($scope, $routeParams, $cookies, episodesService) {

        var promiseEpisodes = episodesService.getEpisodesById($routeParams.animeID);
        promiseEpisodes.then(function(episodes) {
            $scope.episodes = episodes;
        });

        var user = $cookies.getObject('user');

        $scope.addEpisode = function(id_anime, id_episode, key) {
            var user = $cookies.getObject('user');

            var promiseAddEpisode = episodesService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseAddEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.episodes[key].inCollection = 1;

            });
        };

        $scope.removeEpisode = function(id_anime, id_episode, key) {
            var user = $cookies.getObject('user');

            var promiseRemoveEpisode = episodesService.removeEpisodeFromCollection(id_anime, id_episode, user);
            promiseRemoveEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.episodes[key].inCollection = 0;

            });
        }

    });

    app.controller('SearchController', function($scope, $routeParams, searchService, usSpinnerService, $timeout) {
        $timeout(function() {
            usSpinnerService.spin('spinner-1');
        }, 100);
        var promiseSearch = searchService.getSearchResult($routeParams.searchParam);
        promiseSearch.then(function(searchResult) {
            usSpinnerService.stop('spinner-1');
            $scope.listAnimes = searchResult.animes;
            $scope.listMangas = searchResult.mangas;
        });

    });

    app.controller('AuthenticationController', function($rootScope, $scope, $location, $cookies, $route, $window, authenticationService, userService, sAlert) {

        var user = $cookies.getObject('user');
        if ( user != undefined ) $location.path('/');

        $scope.register = function() {
            var login = authenticationService.register($scope.register.username, $scope.register.password, $scope.register.email);
            login.then(function(loginData){
                // On récupère les infos du user à partir de l'ID retourné par le header
                var user = userService.getUserById(loginData.data.id, loginData.data.token, loginData.data.username);
                user.then(function(userData) {
                    // On set le cookie avec quelques infos potentiellement utiles
                    $cookies.putObject('user', {
                        'userID': userData.infos.id,
                        'username' : userData.infos.username,
                        'userEmail': userData.infos.email,
                        'userAddress': userData.infos.address,
                        'userToken': loginData.data.token
                    });
                    $rootScope.userCookie = {
                        'userID': userData.infos.id,
                        'username' : userData.infos.username,
                        'userEmail': userData.infos.email,
                        'userAddress': userData.infos.address,
                        'userToken': loginData.data.token
                    };
                });
            });
        };

        $scope.login = function() {
            var login = authenticationService.login($scope.login.username, $scope.login.password);
            login.then(function(loginData) {
                // Si la connexion est OK
                if ( loginData.status == 200 ) {
                    // On récupère les infos du user à partir de l'ID retourné par le header
                    var user = userService.getUserById(loginData.data.id, loginData.data.token, loginData.data.username);
                    user.then(function(userData) {
                        // On set le cookie avec quelques infos potentiellement utiles
                        $cookies.putObject('user', {
                            'userID': userData.infos.id,
                            'username' : userData.infos.username,
                            'userEmail': userData.infos.email,
                            'userAddress': userData.infos.address,
                            'userToken': loginData.data.token
                        });
                        $rootScope.userCookie = {
                            'userID': userData.infos.id,
                            'username' : userData.infos.username,
                            'userEmail': userData.infos.email,
                            'userAddress': userData.infos.address,
                            'userToken': loginData.data.token
                        };
                    });
                }
            });
            $location.path('/');
        }

    });

    app.controller('CollectionController', function($scope, $cookies, $location, collectionService) {
        var user = $cookies.getObject('user');
        if ( user == undefined ){
            $location.path('/authentification');
            return;
        }

        // Récupère la collection de l'utilisateur
        var promiseCollection = collectionService.getAllCollection(user.userID, user.userToken);

        promiseCollection.then(function(response) {
            if ( response.status == 200 ) {
                $scope.listAnimes = response.infos.animes;
                $scope.listMangas = response.infos.mangas;
            }

        });

    });

    app.controller('ProfileController', function($scope, $routeParams, $cookies, $location, userService, $http, sAlert, Upload) {
      var user = $cookies.getObject('user');
      if ( user == undefined ){
          $location.path('/authentification');
          return;
      }

      var promiseProfile = userService.getUserById(user.userID, user.userToken, $routeParams.username);
      promiseProfile.then(function(response) {

        if ( response.status == 200 ) {
            $scope.user = response.infos;
            $scope.animes = response.animes;
            $scope.mangas = response.mangas;
        } else {
            $location.path('/');
        }

      });

        $scope.updateProfile = function(address, file) {

            if (!address){
                address = undefined;
            }

            if (!file){
                file = {};
            }

            file.upload = Upload.upload({
                method: 'POST',
                url: PATH_MAC+'api/action/update_user',
                headers: {
                    'Client-Service': 'frontend-client',
                    'Auth-Key': 'simplerestapi',
                    'Authorization': user.userToken,
                    'User-ID': user.userID
                },
                file: file,
                data: {
                    address: address
                }
            });

            file.upload.then(function (response) {

                if ( response.data.status == 200 ) {
                    if (response.data.datas.img_profil) {
                        $scope.user.img_profil = response.data.datas.img_profil;
                    }
                    var cookie = $cookies.getObject('user');
                    cookie.userAddress = address;
                    $cookies.putObject('user', cookie);
                    sAlert.success("Profil mis à jour avec succès.").autoRemove();
                } else {
                    sAlert.error(response.data.message).autoRemove();
                }

            }, function (response) {
                sAlert.error(response.data.message).autoRemove();
            });
        };

        $scope.googlePlaces = function() {
            if ($scope.user.address.length > 10) {
                return $http({
                    method: 'GET',
                    url: PATH_MAC + 'api/action/address',
                    headers: {
                        'Client-Service': 'frontend-client',
                        'Auth-Key': 'simplerestapi',
                        'Authorization': user.userToken,
                        'User-ID': user.userID
                    },
                    params: {
                        address: $scope.user.address
                    }
                }).then(function (response) {

                    if (response.data.status == 200)
                        $scope.addressList = response.data.infos;
                    else
                        console.log(response.data.message);

                }, function errorCallback(response) {

                    console.log(response);

                });
            }
        };

        $scope.selectAddress = function(address) {
            $scope.user.address = address;
            $scope.addressList = {};
        }

    });

    app.controller('usersTomeController', function($scope, $routeParams, $http, $cookies, $location, usersTomeService, mangasService, $geolocation, $mdDialog, $timeout, usSpinnerService, sAlert) {

        var user = $cookies.getObject('user');
        if (user == undefined){
            $location.path('/authentification');
            return;
        }
        
        if (user.userAddress){
            $scope.localisation = 'address';

            var latitude = '';
            var longitude = '';
            var promiseUsersTome = usersTomeService.getUsersByTome($routeParams.mangaID, $routeParams.tomeNumber, latitude, longitude);

            promiseUsersTome.then(function(response) {

                if ( response.status == 200 ) {
                    $scope.users = response.infos;
                    $scope.tome = response.tome;
                }
            });
        }else{
            $scope.localisation = 'geolocalisation';

            $timeout(function() {
                usSpinnerService.spin('spinner-1');
            }, 100);

            $geolocation.getCurrentPosition({timeout: 60000}).then(function(data){
                var latitude = data.coords.latitude;
                var longitude = data.coords.longitude;

                var promiseUsersTome = usersTomeService.getUsersByTome($routeParams.mangaID, $routeParams.tomeNumber, latitude, longitude);

                promiseUsersTome.then(function(response) {

                    if ( response.status == 200 ) {
                        usSpinnerService.stop('spinner-1');
                        $scope.users = response.infos;
                        $scope.tome = response.tome;
                    }
                });
            });
        }

        var promiseManga = mangasService.getMangaById($routeParams.mangaID);
        promiseManga.then(function(manga) {
            $scope.title = manga.title;
        });

        $scope.changeLocalisation = function(ev){
            if ($scope.localisation == 'address'){
                if (user.userAddress){
                    var promiseUsersTome = usersTomeService.getUsersByTome($routeParams.mangaID, $routeParams.tomeNumber, '', '');

                    promiseUsersTome.then(function(response) {

                        if ( response.status == 200 ) {
                            $scope.users = response.infos;
                            $scope.tome = response.tome;
                        }
                    });
                }else{
                    $scope.localisation = "geolocalisation";
                    $mdDialog.show(
                        $mdDialog.alert()
                            .parent(angular.element(document.querySelector('#popupContainer')))
                            .clickOutsideToClose(true)
                            .title("Impossible d'effectuer une recherche par votre adresse")
                            .textContent("Votre adresse n'a pas été renseigné. Veuillez vous rendre sur votre profil et renseignez votre adresse pour pouvoir effectuer cette action.")
                            .ariaLabel('Alert address empty')
                            .ok('ok')
                    );

                }
            }else{
                $timeout(function() {
                    usSpinnerService.spin('spinner-1');
                }, 100);

                $geolocation.getCurrentPosition({timeout: 60000}).then(function(data){
                    var latitude = data.coords.latitude;
                    var longitude = data.coords.longitude;

                    var promiseUsersTome = usersTomeService.getUsersByTome($routeParams.mangaID, $routeParams.tomeNumber, latitude, longitude);

                    promiseUsersTome.then(function(response) {

                        if ( response.status == 200 ) {
                            usSpinnerService.stop('spinner-1');
                            $scope.users = response.infos;
                            $scope.tome = response.tome;
                        }
                    });
                });
            }
        }

        $scope.sendRequest = function(userSelected){
            return $http({
                method: 'POST',
                url: PATH_MAC+'api/action/send_request',
                headers: {
                    'Authorization': user.userToken,
                    'User-ID': user.userID
                },
                data: {
                    id_manga: $routeParams.mangaID,
                    username_src : user.username,
                    username_dest: userSelected.username,
                    title: $scope.tome.title,
                    number: $scope.tome.number,
                    couverture: ($scope.tome.couverture_fr ? $scope.tome.couverture_fr : $scope.tome.couverture_jp)
                }
            }).success(function(data){
                sAlert.success("Demande envoyé avec succès !").autoRemove();
            }).error(function(data){
                sAlert.error(data.message).autoRemove();
            });
        }

    });

    app.controller('calendarController', function($scope, calendarService) {

        $scope.calendar = "animes";

        $scope.displayCalendar = function(){
            var calendar = calendarService.getCalendar($scope.calendar);

            calendar.then(function(response) {
                if ( response.status == 200 ) {
                    $scope.days = response.infos;
                }

            });
        }

        $scope.displayCalendar();
    });


    app.controller('suiviController', function($scope, $cookies, sAlert, suiviService, episodesService, tomesService) {

        $scope.maxHeightEpisodes = 'auto';
        $scope.maxHeightTomes = 'auto';
        $scope.maxHeightAnimes = 'auto';
        $scope.maxHeightMangas = 'auto';

        var user = $cookies.getObject('user');
        if ( user == undefined ){
            $location.path('/authentification');
            return;
        }

        var suivi = suiviService.getSuivi(user.userID, user.userToken);

        suivi.then(function(response) {
            if ( response.status == 200 ) {
                $scope.episodes = response.episodes;
                $scope.tomes = response.tomes;
                $scope.animes = response.animes;
                $scope.mangas = response.mangas;

                angular.element(document).ready(function () {
                    $scope.maxHeightEpisodes = getMaxHeight($scope.episodes, "affiche_episode_");
                    $scope.maxHeightTomes = getMaxHeight($scope.tomes, "affiche_tome_");
                    $scope.maxHeightAnimes = getMaxHeight($scope.animes, "affiche_anime_");
                    $scope.maxHeightMangas = getMaxHeight($scope.mangas, "affiche_manga_");
                });
            }
        });

        $scope.addEpisode = function(id_anime, id_episode, key) {
            var user = $cookies.getObject('user');

            var promiseAddEpisode = episodesService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseAddEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 ) {
                    if (response.next_episode[0]) {
                        $scope.episodes[key].number++;
                        $scope.episodes[key].title = response.next_episode[0].title;
                    }else{
                        $scope.episodes.splice(key, 1);
                        if ($scope.episodes.length > 0){
                            document.getElementById("episode_" + key).parentNode.remove();
                        }else{
                            document.getElementById("episode_" + key).parentNode.parentNode.parentNode.parentNode.remove();
                        }
                    }
                }
            });
        };

        $scope.addTome = function(id_manga, id_tome, key) {
            var promiseAddTome = tomesService.setTomeToCollection(id_manga, id_tome, user);
            promiseAddTome.then(function(response) {
                if ( response != undefined && response.status == 201 ) {
                    if (response.next_tome[0]) {
                        $scope.tomes[key].number++;
                        $scope.tomes[key].title = response.next_tome[0].title;
                        $scope.tomes[key].couverture = response.next_tome[0].couverture;
                    }else{
                        $scope.tomes.splice(key, 1);
                        if ($scope.tomes.length > 0){
                            document.getElementById("tome_" + key).parentNode.remove();
                        }else{
                            document.getElementById("tome_" + key).parentNode.parentNode.parentNode.parentNode.remove();
                        }
                    }
                }
            });
        };

        $scope.addAnime = function(id_anime, id_episode, key) {
            var user = $cookies.getObject('user');

            var promiseAddEpisode = episodesService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseAddEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 ) {
                    if (response.next_episode[0]) {
                        $scope.episodes.push({
                            'id_anime' : response.next_episode[0].id_anime,
                            'anime_title' : response.next_episode[0].anime_title,
                            'img_affiche' : response.next_episode[0].img_affiche,
                            'number' : response.next_episode[0].number,
                            'title' : response.next_episode[0].title,
                            'diffusion' : response.next_episode[0].diffusion });
                    }
                    $scope.animes.splice(key, 1);
                    if ($scope.animes.length > 0) {
                        document.getElementById("anime_" + key).parentNode.remove();
                    }else{
                        document.getElementById("anime_" + key).parentNode.parentNode.parentNode.parentNode.remove();
                    }
                }
            });
        };

        $scope.addManga = function(id_manga, id_tome, key) {
            var promiseAddTome = tomesService.setTomeToCollection(id_manga, id_tome, user);
            promiseAddTome.then(function(response) {
                if ( response != undefined && response.status == 201 ) {
                    if (response.next_tome[0]) {
                        $scope.tomes.push({
                            'id_manga' : response.next_tome[0].id_manga,
                            'manga_title' : response.next_tome[0].manga_title,
                            'number' : response.next_tome[0].number,
                            'title' : response.next_tome[0].title,
                            'couverture' : response.next_tome[0].couverture });
                    }
                    $scope.mangas.splice(key, 1);
                    if ($scope.mangas.length > 0) {
                        document.getElementById("manga_" + key).parentNode.remove();
                    }else{
                        document.getElementById("manga_" + key).parentNode.parentNode.parentNode.parentNode.remove();
                    }
                }
            });
        };
    });

    /*
     * Création de filtre
     *
     */
    app.filter('prettycomma', function() {
        return function(input) {
            if ( input != undefined )
                return input.replace(/,/g, ', ');
        };
    });

    function getMaxHeight(array, id){
        var maxHeight = 0;
        angular.forEach(array, function(value, key) {
            var currentHeight = document.getElementById(id+key).clientHeight;
            if (currentHeight > maxHeight) {
                maxHeight = currentHeight;
            }
        });
        return maxHeight+'px';
    }

})(window.angular);
