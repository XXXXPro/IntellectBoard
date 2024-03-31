{% macro input(name, value, size, maxlen,extdata) %}
  <input type="text" name="{{ name }}" value="{{ value|e }}" size="{{ size|default(40) }}" maxlength="{{ maxlen|default(40) }}" {{ extdata|raw }}/>
{% endmacro %}

{% macro password(name, value, size, maxlen) %}
  <input type="password" name="{{ name }}" value="{{ value|e }}" size="{{ size|default(40) }}" maxlength="{{ maxlen|default(40) }}" />
{% endmacro %}

{% macro hidden(name, value) %}
  <input type="hidden" name="{{ name }}" value="{{ value|e }}" />
{% endmacro %}

{% macro checkbox(name, value, realvalue) %}
  <input type="checkbox" name="{{ name }}" value="{{ value|e }}" {% if (value==realvalue) %}checked="checked"{% endif %} />
{% endmacro %}

{% macro radio(name, values, realvalue) %}
{% for value,descr in values
%}<label><input type="radio" name="{{ name }}" value="{{ value|e }}" {% if (value==realvalue) %}checked{% endif %} />{{ descr }}</label> {%
endfor %}
{% endmacro %}

{% macro textarea(name, value, rows, cols) %}
  <textarea name="{{ name }}" rows="{{ rows|default(10) }}" cols="{{ cols|default(40) }}">{{ value }}</textarea>
{% endmacro %}

{% macro select(name, realvalue, values, rows, multiple) %}
<select name="{{ name }}"{% if rows %} size="{{ rows }}"{% endif %}{% if multiple %} multiple="1"{% endif %}>
{% for key,value in values %}
<option value="{{ key }}" {% if (key|trim==realvalue|trim) %}selected="selected"{% endif %}>
{{ value|e }}
</option>
{% endfor %}
</select>
{% endmacro %}

{% macro captcha(key,timecode,captcha_data) %}
{% if get_opt('captcha')==1 %}
<input type="text" name="captcha_value" size="8" autocomplete="off" />
<input type="hidden" name="captcha_key" value="{{ key }}"/>
<input type="hidden" name="captcha_timecode" value="{{ timecode }}"/>
<img class="captcha" src="data:image/jpeg;base64,{{ captcha_data }}" alt="Включите графику, чтобы увидеть код!" />
{% elseif get_opt('captcha')==2 %}
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<span class="g-recaptcha" data-sitekey="{{ get_opt('captcha_public_key') }}"></span>
{% endif %}
{% endmacro %}

{% macro pages(pagedata,prefix,notext) %}
{% if (pagedata.pages==1) %}
{% if not notext %}Одна страница{% endif %}
{% else %}
{% if not notext %}Страницы: {% endif %}<ul>
{% for i in 1..pagedata.pages %}
{% if i==pagedata.page and not notext %}
<li><b>{{ i }}</b></li>
{% elseif i==1 and prefix %}
<li><a href="{{ prefix }}">{{ i }}</a></li>
{% elseif i==1 %}
<li><a href="./">{{ i }}</a></li>
{% else %}
<li><a href="{{ prefix }}{{ i }}.htm">{{ i }}</a></li>
{% endif %}
{% endfor %}
</ul>
{% endif %}
{% endmacro %}

{% macro menu(data) %}
{% if data %}
<ul>{% for item in data %}
<li><a href="{{ item.url }}">{{ item.title }}</a></li>
{% endfor %}
</ul>
{% endif %}
{% endmacro %}

{% macro messages(data) %}
{% for item in data %}
{% if item.level==3 %}<div class="msg_error">{{ item.text }}</div>{% endif %}
{% if item.level==2 %}<div class="msg_warn">{{ item.text }}</div>{% endif %}
{% if item.level==1 %}<div class="msg_ok">{{ item.text }}</div>{% endif %}
{% endfor %}
{% endmacro %}

{% macro location(data,rss) %}
{% if data|length > 0 %}
<nav>
{% if rss[0] %}<a class="rss_link" href="{{ rss[0].url }}" title="{{ rss[0].title }}"><i class="fa fa-rss-square"></i></a>{% endif %}
<ul class="location_path" itemscope itemtype="http://schema.org/BreadcrumbList">
{% for item in data %}
{% if item[1] %}<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">{% if not loop.first %} &raquo; {% endif %}
<a href="{{ item[1] }}" itemtype="https://schema.org/Thing" itemprop="item"><span itemprop="name">{{ item[0] }}</span></a><meta itemprop="position" content="{{ loop.index }}"></li>
{% else %}<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"> &raquo; <span itemtype="https://schema.org/Thing">
<span itemprop="name">{{ item[0] }}</span><link itemprop="item" href="#" /></span><meta itemprop="position" content="{{ loop.index }}"></li>
{% endif %}
{% endfor %}
</ul></nav>
{% endif %}
{% endmacro %}

{% macro user(uname,uid,gender,classes) %}
{% if uid>3 %}<a href="{{ url(sprintf(get_opt('user_hurl'),uid,uname)) }}" class="username{% if gender=='M' %} male{% elseif gender=='F' %} female{% endif %} {{ classes }}">{{ uname }}</a>{% else %}<span class="username">{{ uname }}</span>{% endif %}
{% endmacro %}

{% macro avatar(id,avatar,alt,classes) %}
{% if avatar=='gif' %}{% set avfile = id~'.gif' %}
{% elseif avatar=='jpg' %}{% set avfile = id~'.jpg' %}
{% elseif avatar=='png' %}{% set avfile = id~'.png' %}
{% else %}{% set avfile = 'no.jpg' %}{% set alt = 'Нет' %}{% endif %}
<img class="avatar {{ classes }}" src="{{ url('f/av') }}/{{ avfile }}" alt="{{ alt }}" />{%
  endmacro %}

{% macro photo(id,avatar,alt,classes) %}
{% if avatar=='gif' %}{% set avfile = id~'.gif' %}
{% elseif avatar=='jpg' %}{% set avfile = id~'.jpg' %}
{% elseif avatar=='png' %}{% set avfile = id~'.png' %}
{% else %}{% set avfile = 'no.jpg' %}{% endif %}
<img class="userphoto {{ classes }}" src="{{ url('f/ph') }}/{{ avfile }}" alt="{{ alt }}" />
{% endmacro %}

{% macro filesize(size) %}
{% if size > 1024*1024*10 %}{{ sprintf('%d Мб',size/(1024*1024)) }}{%
elseif size > 1024*10 %}{{ sprintf('%d Кб',size/1024) }}{%
else %}{{ size|incline('%d байт','%d байта','%d байтов') }}{% endif %}
{% endmacro %}

{% macro sub_block(blockdata) %}
{% for tpl, data in blockdata %}
{% include tpl with { 'data' : data } %}
{% endfor %}
{% endmacro %}
