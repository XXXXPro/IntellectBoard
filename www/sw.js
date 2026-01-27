const CACHE_NAME = 'intb_cache';
const BASE_URL = self.registration.scope;

const PRECACHED_DATA = [
  BASE_URL+'offline/empty.js',
  BASE_URL+'offline/empty.gif',
  BASE_URL+'offline/noimage.png',
  BASE_URL+'offline/offline.htm',
  BASE_URL+'f/av/no.jpg',
  BASE_URL+'rules.htm',
  BASE_URL+'team.htm',
];

const CACHE_PATTERNS = {
  'networkOrEmptyJS': [
    'https://www.googletagmanager.com/gtag/js',
    'https://mc.yandex.ru/metrika/watch.js',
    'https://connect.facebook.net/en_US/fbevents.js'
  ],
  'networkOrEmptyGif': [
    BASE_URL+'cron.php',
    'https://mc.yandex.ru/watch/',
    'https://www.facebook.com/tr?id='
  ],
  'cacheFirst': [
    BASE_URL+'fa/webfonts/fa-regular-400.woff2',
    BASE_URL+'fa/webfonts/fa-solid-900.woff2',
    BASE_URL+'fa/css/fontawesome-all.min.css',
    /\/s\/\w+\/\w+\.(png|jpg|jpeg|gif|svg|ico|webp|woff|woff2|ttf|eot)$/,
    /\/js\/.*\w+\.(png|jpg|jpeg|gif|svg|ico|webp|woff|woff2|ttf|eot)$/,
    BASE_URL+'js/jquery.min.js',
    BASE_URL+'f/av/no.jpg',
    /\/sm\/\w+\.png$/,
    /\/s\/.*\/\w+\.(css|js)(\?\d+)?$/,
    /\/js\/.+\.(js|css)(\?\d+)?$/
  ],
  'networkOrAvatar': [
    /\/f\/av\/\d+\.(png|jpg|jpeg|gif|svg)$/,
  ],
  'networkOrNoImage': [
    /\/f\/up\/.*\.(png|jpg|jpeg|gif|svg|webp)$/,
  ],
  'cacheRefresh': [
  ],
  'networkOrOffline': [
    /\/user\//,
    /\/users\//,
    /\/online\//,
    /\/search\//,
    /\.oauth\//,
    /\.well-known\//,
    /\/mark_all\.htm$/,
    /\/f\/up\/.*\/.*$/,
    /\/moderate\//,
    /\/[\w\-]+\/[\w\-]+\/change_mode\.htm/
  ]
};

// В событии install
self.addEventListener('install', event => {
  // console.log(self.registration.scope);
  self.skipWaiting(); // Пропустить фазу ожидания
  
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      // console.log('Adding to cache...');
      return cache.addAll(PRECACHED_DATA);
    })
  );
});

// В событии activate
self.addEventListener('activate', event => {
  event.waitUntil(
    Promise.all([
      self.clients.claim(), // Немедленный контроль над клиентами
      // Очистка старых кешей
      caches.keys().then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              // console.log('Clearing old cache '+cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
    ])
  );
});

// Стратегия 1: cacheFirst - отдаём из кеша сразу, не обновляем
function cacheFirst(request) {
  return caches.open(CACHE_NAME).then(function(cache) {
    return cache.match(request).then(function(cachedResponse) {
      if (cachedResponse) {
        return cachedResponse;
      }
      // Если нет в кеше, делаем запрос в сеть и сохраняем в кеш
      return fetch(request).then(function(networkResponse) {
        if (networkResponse && networkResponse.status === 200 && request.method==="GET") {
          cache.put(request, networkResponse.clone());
        }
        return networkResponse;
      });
    });
  });
}

// Стратегия 2: cacheRefresh - сразу из кеша, потом обновление в фоне
function cacheRefresh(request) {
  return caches.open(CACHE_NAME).then(function(cache) {
    return cache.match(request).then(function(cachedResponse) {
      // Фоновое обновление кеша
      var fetchPromise = fetch(request).then(function(networkResponse) {
        if (networkResponse && networkResponse.status === 200 && request.method==="GET") {
          cache.put(request, networkResponse.clone());
        }
        return networkResponse;
      }).catch(function(error) {
        console.error('Ошибка при фоновом обновлении:', error);
      });

      // Немедленно возвращаем кешированный ответ, если есть
      if (cachedResponse) {
        return cachedResponse;
      }
      
      // Если нет в кеше, ждем сетевой ответ
      return fetchPromise;
    });
  });
}

// Стратегия 3: networkFallback - сначала сеть, потом fallback URL
function networkFallback(request, fallbackUrl) {
  return fetch(request).then(function(networkResponse) {
    // Если сетевой запрос успешен, возвращаем его
    if ((networkResponse && networkResponse.status >= 200) || networkResponse.type==='opaque' || networkResponse.type==='opaqueredirect') {
      return networkResponse;
    }
    
    // Если ошибка сети, пробуем fallback
    return Promise.reject('Network request failed');
  }).catch(function(error) {
    console.log('Используем fallback для:', request.url);
    return caches.open(CACHE_NAME).then(function(cache) {
      return cache.match(new Request(fallbackUrl)).then(function(fallbackResponse) {
        if (fallbackResponse) {
          return fallbackResponse;
        }
        return new Response('Страница не найдена', {
          status: 404,
          statusText: 'Not Found'
        });
      });
    });
  });
}

// Стратегия 4: networkFirst - сначала сеть, потом кеш, потом fallback
function networkFirst(request, fallbackUrl) {
  return fetch(request).then(function(networkResponse) {
    // Сохраняем успешный ответ в кеш
    if ((networkResponse && networkResponse.status === 200) || networkResponse.type==='opaqueredirect') {
      return caches.open(CACHE_NAME).then(function(cache) {
        if (request.method==="GET") cache.put(request, networkResponse.clone());
        return networkResponse;
      });
    }
    if ((networkResponse && networkResponse.status > 200) || networkResponse.type==='opaque') {
      return networkResponse;
    }
    
    // Если статус не 200, пробуем кеш
    return Promise.reject('Network response not ok');
  }).catch(function(error) {
    console.log('Сеть недоступна, ищем в кеше:', request.url);
    
    return caches.open(CACHE_NAME).then(function(cache) {
      return cache.match(request).then(function(cachedResponse) {
        if (cachedResponse) {
          return cachedResponse;
        }
        
        // Если нет в кеше, пробуем fallback
        if (fallbackUrl) {
          return cache.match(new Request(fallbackUrl)).then(function(fallbackResponse) {
            if (fallbackResponse) {
              return fallbackResponse;
            }
            return new Response('Страница не найдена', {
              status: 404,
              statusText: 'Not Found'
            });
          });
        }
        
        return new Response('Сеть недоступна и кеш пуст', {
          status: 503,
          statusText: 'Service Unavailable'
        });
      });
    });
  });
}

function networkFirstWithOffline(request) {
  let fallbackUrl = BASE_URL+'offline/offline.htm';
  if (/\.(jpe?g|gif|png|webp|svg)/.test(request.url)) {
    fallbackUrl = BASE_URL+'offline/noimage.png';
  }
  if (request.url.endsWith('.js') || request.url.endsWith('.css')) {
    fallbackUrl = BASE_URL+'offline/empty.js';
  }
  // локальные запросы — кешируем, для внешних — используем fallback
  if (request.url.startsWith(BASE_URL)) return networkFirst(request,fallbackUrl);
  else return networkFallback(request,fallbackUrl);
}

networkOrOffline = (request)=>networkFallback(request,BASE_URL+'offline/offline.htm');
networkOrEmptyJS = (request)=>networkFallback(request,BASE_URL+'offline/empty.js');
networkOrEmptyGif = (request)=>networkFallback(request,BASE_URL+'offline/empty.gif');
networkOrNoImage = (request)=>networkFallback(request,BASE_URL+'offline/noimage.png');
networkOrAvatar = (request)=>networkFallback(request,BASE_URL+'f/av/no.jpg')


// Обработчик сообщений от клиентов
self.addEventListener('message', function(event) {
  var data = event.data;
  
  if (data.type === 'CLEAR_CACHE') {
    caches.delete(CACHE_NAME).then(function() {
      event.ports[0].postMessage({ success: true });
    }).catch(function(error) {
      event.ports[0].postMessage({ success: false, error: error });
    });
  }
  
  if (data.type === 'UPDATE_CACHE') {
    var urls = data.urls || [];
    caches.open(CACHE_NAME).then(function(cache) {
      return Promise.all(
        urls.map(function(url) {
          return fetch(url).then(function(response) {
            if (response.ok) {
              return cache.put(url, response);
            }
          });
        })
      );
    }).then(function() {
      event.ports[0].postMessage({ success: true });
    }).catch(function(error) {
      event.ports[0].postMessage({ success: false, error: error });
    });
  }
});

function chooseStrategy(url) {
  for (strategy in CACHE_PATTERNS) {
    // function for pattern matching: is pattern is regexp, use RegExp.test function, if pattern is string, check if URL begins with pattern
    const match = pattern => (pattern instanceof RegExp) ? url.startsWith(BASE_URL) && pattern.test(url) : url.startsWith(pattern); 
    if (CACHE_PATTERNS[strategy].some(match)) {
//      console.log('Using strategy '+strategy+' for '+url);
      return strategy;
    }
  }
//  console.log('Default strategy for '+url);
  return 'networkFirstWithOffline';
}

self.addEventListener('fetch', evt => {
  if (evt.request.method!=='GET') evt.respondWith(fetch(evt.request));
  else {
    const result = chooseStrategy(evt.request.url);
    const forum_re = new RegExp(BASE_URL+'[\\w+\\-]+/(\d+\.htm)?$');
    evt.respondWith(self[result](evt.request));
  }
});
