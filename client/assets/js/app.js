(function(angular) {
    'use strict';

    var app = angular.module('ConnectMangasApp', ['ngRoute', 'ngMaterial', 'ngCookies', 'sAlert']);

    const PATH_JG_HOME = "http://localhost/connectmangas/";
    const PATH_JG_TAF = "http://localhost/jg/test-fusion-connectmangas_v2/server/";
    const PATH_MAC = "http://localhost:8888/connectmangas/server/";

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
        }).when('/search/:searchParam', {
            templateUrl: 'client/pages/search.html',
            controller: 'SearchController'
        }).when('/authentication', {
            templateUrl: 'client/pages/authentication.html',
            controller: 'AuthenticationController'
        }).when('/collection', {
            templateUrl: 'client/pages/collection.html',
            controller: 'CollectionController'
        }).when('/profile/:username', {
            templateUrl: 'client/pages/profile.html',
            controller: 'ProfileController',
            requireAuth: true
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
                    url: PATH_JG_TAF+'api/action/manga/'+id,
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
                    url: PATH_JG_TAF+'api/action/add_collection_manga',
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

                    var addMangaResult = response.data;
                    return addMangaResult;

                }, function errorCallback(response) {

                    if ( response.status == 403 && response.data.message == "Already in collection." )
                      sAlert.error(response.data.message).autoRemove();

                });
            },
            removeMangaFromCollection: function(id_manga, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_JG_TAF+'api/action/delete_collection_manga',
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

                    var removeMangaResult = response.data;
                    return removeMangaResult;

                }, function errorCallback(response) {

                    console.log(response);

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
                    url: PATH_JG_TAF+'api/action/tomes/'+id,
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
                    url: PATH_JG_TAF+'api/action/add_collection_tome',
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

                    var addTomeResult = response.data;
                    return addTomeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error(response.data.message).autoRemove();

                });
            },
            removeTomeFromCollection: function(id_manga, id_tome, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_JG_TAF+'api/action/delete_collection_tome',
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

                    var removeTomeResult = response.data;
                    return removeTomeResult;

                }, function errorCallback(response) {

                    console.log(response);

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
                    url: PATH_JG_TAF+'api/action/anime/'+id,
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
                    url: PATH_JG_TAF+'api/action/add_collection_anime',
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

                    var addAnimeResult = response.data;
                    return addAnimeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error(response.data.message).autoRemove();

                });
            },
            removeAnimeFromCollection: function(id_anime, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_JG_TAF+'api/action/delete_collection_anime',
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

                    var removeAnimeResult = response.data;
                    return removeAnimeResult;

                }, function errorCallback(response) {

                    console.log(response);

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
                    url: PATH_JG_TAF+'api/action/episodes/'+id,
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
                    method: 'DELETE',
                    url: PATH_JG_TAF+'api/action/add_collection_episode',
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

                    var addEpisodeResult = response.data;
                    return addEpisodeResult;

                }, function errorCallback(response) {

                  if ( response.status == 403 && response.data.message == "Already in collection." )
                    sAlert.error(response.data.message).autoRemove();

                });
            },
            removeEpisodeFromCollection: function(id_anime, id_episode, user) {
                return $http({
                    method: 'DELETE',
                    url: PATH_JG_TAF+'api/action/delete_collection_episode',
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

                    var removeEpisodeResult = response.data;
                    return removeEpisodeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
            }
        }

    });

    app.factory('searchService', function($http) {

        return {
            getSearchResult: function(param) {
                return $http.get(PATH_JG_TAF+'api/action/search/'+param).then(function(response) {

                    var searchResult = response.data;
                    return searchResult;

                });
            }
        }

    });

    app.factory('authenticationService', function($http, sAlert, $location, $window) {
        return {
            register: function(username, password, email){
                return $http({
                    method: 'POST',
                    url: PATH_JG_TAF+'api/action/register',
                    data: {username: username, password: password, email: email}
                }).success(function(data){
                    sAlert.success(data.message).autoRemove();
                    $location.path('/');
                }).error(function(data){
                    sAlert.error(data.message).autoRemove();
                });

            },
            login : function(username, password){
                return $http({
                    method: 'POST',
                    url: PATH_JG_TAF+'api/action/login',
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
                    url: PATH_JG_TAF+'api/action/profil/'+username,
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
            getAllCollection: function() {

            },
            checkIfInCollection: function() {
              
            }
        }

    });

    /*
     * Gestion des controllers
     * @params $scope, $routeParams, factoryService
     */
    app.controller('AppCtrl', function($scope, $cookies, $location, $window, $rootScope) {

        // DO SOMETHING
        var userCookie = $cookies.getObject('user');
        $scope.userCookie = userCookie;

        $scope.logout = function() {
            $cookies.remove('user');
            $window.location.reload();
            //$scope.$apply();
        }

        $scope.searchFunc = function() {
            var search = $scope.mySearch.replace(/ /g,"_");
            $location.path('/search/'+search);
        };

    });

    app.controller('HomeController', function($scope) {
        $scope.message = "This is the home page";
    });

    app.controller('MangaController', function($scope, $routeParams, $cookies, mangasService, sAlert) {

        var promiseManga = mangasService.getMangaById($routeParams.mangaID);
        promiseManga.then(function(manga) {
            $scope.manga = manga;
        });

        var user = $cookies.getObject('user');

        $scope.addManga = (id_manga) => {
          var promiseAddManga = mangasService.setMangaToCollection(id_manga, user);
          promiseAddManga.then(function(response) {

              if ( response != undefined && response.status == 201 )
                $scope.isMangaInCollection = true;


          });
        }

        $scope.removeManga = (id_manga) => {
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

        $scope.addTome = function(id_manga, id_tome) {
            var user = $cookies.getObject('user');

            var promiseAddTome = tomesService.setTomeToCollection(id_manga, id_tome, user);
            promiseAddTome.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isTomeInCollection = true;

            });
        }

        $scope.removeTome = function(id_manga, id_tome) {
            var user = $cookies.getObject('user');

            var promiseRemoveTome = tomesService.removeTomeFromCollection(id_manga, id_tome, user);
            promiseRemoveTome.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isTomeInCollection = false;

            });
        }

    });

    app.controller('AnimeController', function($scope, $routeParams, $cookies, animesService) {

        var promiseAnime = animesService.getAnimeById($routeParams.animeID);
        promiseAnime.then(function(anime) {
            $scope.anime = anime;
        });

        var user = $cookies.getObject('user');

        $scope.addAnime = function(id_anime) {
            var promiseAddAnime = animesService.setAnimeToCollection(id_anime, user);
            promiseAddAnime.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isAnimeInCollection = true;

            });
        }

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

        $scope.addEpisode = function(id_anime, id_episode) {
            var user = $cookies.getObject('user');

            var promiseAddEpisode = episodesService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseAddEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isEpisodeInCollection = true;

            });
        }

        $scope.removeEpisode = function(id_anime, id_episode) {
            var user = $cookies.getObject('user');

            var promiseRemoveEpisode = episodesService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseRemoveEpisode.then(function(response) {

                if ( response != undefined && response.status == 201 )
                    $scope.isEpisodeInCollection = false;

            });
        }

    });

    app.controller('SearchController', function($scope, $routeParams, searchService) {

        var promiseSearch = searchService.getSearchResult($routeParams.searchParam);
        promiseSearch.then(function(searchResult) {
            $scope.listAnimes = searchResult.animes;
            $scope.listMangas = searchResult.mangas;
        });

    });

    app.controller('AuthenticationController', function($scope, $location, $cookies, $route, $window, authenticationService, userService) {

        $scope.register = function() {
            authenticationService.register($scope.register.username, $scope.register.password, $scope.register.email);
            //$location.path('/');

            // À voir si on fait un cookie au register
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
                            'userToken': loginData.data.token
                        });
                    });
                }

            });
            $location.path('/');
        }

    });

    app.controller('CollectionController', function($scope, $cookies, $location, collectionService) {
        var user = $cookies.getObject('user');
        if ( user == undefined ) $location.path('/authentication');

        // Récupère la collection de l'utilisateur

    });

    app.controller('ProfileController', function($scope, $routeParams, $cookies, $location, userService) {
      var user = $cookies.getObject('user');
      if ( user == undefined ) $location.path('/authentication');

      var promiseProfile = userService.getUserById(user.userID, user.userToken, $routeParams.username);
      promiseProfile.then(function(response) {

        if ( response.status == 200 ) {
          $scope.user = response.infos;
        }

      });

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

})(window.angular);
