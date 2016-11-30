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
    app.factory('mangasService', function($http, $cookies) {

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
            }
        }

    });

    app.factory('tomesService', function($http, $cookies) {

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
            }
        }

    });

    app.factory('animesService', function($http, $cookies) {

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
            }
        }

    });

    app.factory('episodesService', function($http, $cookies) {

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
            }
        }

    });

    app.factory('searchService', function($http) {

        return {
            getSearchResult: function(param) {
                return $http.get(PATH_MAC+'api/action/search/'+param).then(function(response) {

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
                    url: PATH_MAC+'api/action/register',
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
                    url: PATH_MAC+'api/action/login',
                    data: {username: username, password: password}
                }).success(function(data){
                    sAlert.success(data.message).autoRemove();
                    $window.location.reload();
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

                    var addMangaResult = response.data;
                    return addMangaResult;

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

                    var addAnimeResult = response.data;
                    return addAnimeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            setTomeToCollection: function(id_manga, id_tome) {
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

                    var addTomeResult = response.data;
                    return addTomeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            setEpisodeToCollection: function(id_anime, id_episode) {
                return $http({
                    method: 'DELETE',
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

                    var addEpisodeResult = response.data;
                    return addEpisodeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            getCollection: function() {

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

                    var removeMangaResult = response.data;
                    return removeMangaResult;

                }, function errorCallback(response) {

                    console.log(response);

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

                    var removeAnimeResult = response.data;
                    return removeAnimeResult;

                }, function errorCallback(response) {

                    console.log(response);

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

                    var removeTomeResult = response.data;
                    return removeTomeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
            },
            removeEpisodeFromCollection: function() {
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

                    var removeEpisodeResult = response.data;
                    return removeEpisodeResult;

                }, function errorCallback(response) {

                    console.log(response);

                });
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

    });

    app.controller('HomeController', function($scope) {
        $scope.message = "This is the home page";
    });

    app.controller('MangaController', function($scope, $routeParams, mangasService) {

        var promiseManga = mangasService.getMangaById($routeParams.mangaID);
        promiseManga.then(function(manga) {
            $scope.manga = manga;
        });

    });

    app.controller('TomesController', function($scope, $routeParams, tomesService) {

        var promiseTomes = tomesService.getTomesById($routeParams.mangaID);
        promiseTomes.then(function(tomes) {
            $scope.tomes = tomes;
        });

    });

    app.controller('AnimeController', function($scope, $routeParams, animesService) {

        var promiseAnime = animesService.getAnimeById($routeParams.animeID);
        promiseAnime.then(function(anime) {
            $scope.anime = anime;
        });

    });

    app.controller('EpisodesController', function($scope, $routeParams, episodesService) {

        var promiseEpisodes = episodesService.getEpisodesById($routeParams.animeID);
        promiseEpisodes.then(function(episodes) {
            $scope.episodes = episodes;
        });

    });

    app.controller('SearchController', function($scope, $routeParams, searchService) {

        var promiseSearch = searchService.getSearchResult($routeParams.searchParam);
        promiseSearch.then(function(searchResult) {
            $scope.listAnimes = searchResult.animes;
            $scope.listMangas = searchResult.mangas;
        });

    });

    app.controller('AuthenticationController', function($scope, $location, $cookies, $route, authenticationService, userService) {

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
            // $route.reload();
            $location.path('/');
        }

    });

    app.controller('CollectionController', function($scope, collectionService, $cookies) {

        $scope.isMangaInCollection = false;
        $scope.isAnimeInCollection = false;
        $scope.isTomeInCollection = false;
        $scope.isEpisodeInCollection = false;

        $scope.addManga = function(id_manga) {
            var user = $cookies.getObject('user');

            var promiseAddManga = collectionService.setMangaToCollection(id_manga, user);
            promiseAddManga.then(function(response) {

                if ( response.status == 201 )
                    $scope.isMangaInCollection = true;


            });
        };

        $scope.addAnime = function(id_anime) {
            var user = $cookies.getObject('user');

            var promiseAddAnime = collectionService.setAnimeToCollection(id_anime, user);
            promiseAddAnime.then(function(response) {

                if ( response.status == 201 )
                    $scope.isAnimeInCollection = true;

            });
        };

        $scope.addTome = function(id_manga, id_tome) {
            var user = $cookies.getObject('user');

            var promiseAddTome = collectionService.setTomeToCollection(id_manga, id_tome, user);
            promiseAddTome.then(function(response) {

                if ( response.status == 201 )
                    $scope.isTomeInCollection = true;

            });
        };

        $scope.addEpisode = function(id_anime, id_episode) {
            var user = $cookies.getObject('user');

            var promiseAddEpisode = collectionService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseAddEpisode.then(function(response) {

                if ( response.status == 201 )
                    $scope.isEpisodeInCollection = true;

            });
        };

        $scope.removeManga = function(id_manga) {
            var user = $cookies.getObject('user');

            var promiseRemoveManga = collectionService.removeMangaFromCollection(id_manga, user);
            promiseRemoveManga.then(function(response) {

                if ( response.status == 201 )
                    $scope.isMangaInCollection = false;

            });
        };

        $scope.removeAnime = function(id_anime) {
            var user = $cookies.getObject('user');

            var promiseRemoveAnime = collectionService.removeAnimeFromCollection(id_anime, user);
            promiseRemoveAnime.then(function(response) {

                if ( response.status == 201 )
                    $scope.isAnimeInCollection = false;

            });
        };

        $scope.removeTome = function(id_manga, id_tome) {
            var user = $cookies.getObject('user');

            var promiseRemoveTome = collectionService.removeTomeFromCollection(id_manga, id_tome, user);
            promiseRemoveTome.then(function(response) {

                if ( response.status == 201 )
                    $scope.isTomeInCollection = false;

            });
        };

        $scope.removeEpisode = function(id_anime, id_episode) {
            var user = $cookies.getObject('user');

            var promiseRemoveEpisode = collectionService.setEpisodeToCollection(id_anime, id_episode, user);
            promiseRemoveEpisode.then(function(response) {

                if ( response.status == 201 )
                    $scope.isEpisodeInCollection = false;

            });
        }

    });

    app.controller('ProfileController', function($scope, $routeParams, $cookies, userService) {
      var user = $cookies.getObject('user');

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
