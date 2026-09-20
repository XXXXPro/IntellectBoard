<div id="cookie-consent-banner" class="cookie-notice" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background-color: #2d3748; color: #ffffff; padding: 15px; z-index: 1000; font-family: sans-serif; justify-content: center; align-items: center;">
    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; flex-wrap: wrap; text-align: center; width: 100%; max-width: 1200px; margin: 0 auto;">
        <p style="margin: 0;">Этот сайт использует файлы cookie для сбора аналитики и улучшения качества работы. Продолжая, вы соглашаетесь с нашей <a href="/privacy_policy.htm" target="_blank" rel="noopener noreferrer" style="color: #ffffff; text-decoration: underline;">Политикой конфиденциальности</a>.</p>
        <div style="display: flex; gap: 10px; flex-shrink: 0;">
            <button id="cookie-consent-accept" style="background-color: #4caf50; color: #ffffff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">Принять</button>
            <button id="cookie-consent-reject" style="background-color: #f44336; color: #ffffff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px;">Отклонить</button>

        </div>
    </div>
</div>

<script id="cookie-consent-logic" type="text/javascript">
(function() {
    const COOKIE_NAME = 'IntB_agree_pd';
    const STORAGE_KEY = 'IntB_reject_date';
    const DOMAIN_NAME = 'intbpro.ru';
    const MAX_REJECTED_DAYS = 60;
    const banner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('cookie-consent-accept');
    const rejectBtn = document.getElementById('cookie-consent-reject');


    // ИСПРАВЛЕНО: Более надежная функция для чтения cookie,
    // которая не зависит от пробелов между парами ключ-значение.
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/; domain=" + DOMAIN_NAME + "; SameSite=Lax";
    }

    function loadScripts() {
        const scriptsContainer = document.createElement('div');
        const scriptsString = "<!-- Yandex.Metrika counter --> <script> (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, \"script\", \"https:\/\/mc.yandex.ru\/metrika\/tag.js\", \"ym\"); ym(51645944, \"init\", { id:51645944, clickmap:true, trackLinks:true, accurateTrackBounce:true }); <\/script> <noscript><div><img src=\"https:\/\/mc.yandex.ru\/watch\/51645944\" style=\"position:absolute; left:-9999px;\" alt=\"\" \/><\/div><\/noscript> <!-- \/Yandex.Metrika counter -->";        
        scriptsContainer.innerHTML = scriptsString;
        
        Array.from(scriptsContainer.querySelectorAll('script')).forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            document.body.appendChild(newScript);
        });
    }

    function deleteAllCookies() {
        // Получаем все куки как одну строку и разбиваем на массив пар "имя=значение"
        const cookies = document.cookie.split(";");

        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i];
            const eqPos = cookie.indexOf("=");
            const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            
            // Устанавливаем куку с тем же именем и устаревшей датой (-1 день)
            document.cookie = name + '=; Path=/; domain=' + DOMAIN_NAME + '; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
    }    

    const now = new Date();
    const rejDateStr = localStorage.getItem(STORAGE_KEY);

    const cookieValue = getCookie(COOKIE_NAME);
    consent = parseInt(cookieValue) & 2;
    let rejected = false;
    if (rejDateStr) {
       const rejDate = new Date(rejDateStr);
       rejected = now.getTime() - rejDate.getTime() < MAX_REJECTED_DAYS * 24 * 60 * 60 * 1000;
    }

    if (!consent && !rejected) {
        banner.style.display = 'flex'; // ИСПРАВЛЕНО: Используется правильный тип отображения
    } else if (consent === 2) {
        loadScripts();
    }

    acceptBtn.addEventListener('click', function() {
        const newValue = parseInt(cookieValue) | 2;
        setCookie(COOKIE_NAME, newValue, 365);
        banner.style.display = 'none';
        loadScripts();
        localStorage.removeItem(STORAGE_KEY);
    });

    rejectBtn.addEventListener('click', function() {
        localStorage.setItem(STORAGE_KEY, now.toISOString());
        banner.style.display = 'none';
        deleteAllCookies();
    });


    document.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'cookie-consent-open') {
            event.preventDefault();
            banner.style.display = 'flex'; // ИСПРАВЛЕНО: Используется правильный тип отображения
        }
    });

})();
</script>
