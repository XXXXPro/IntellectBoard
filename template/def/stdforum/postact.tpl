<ul class="postact noprint">
{% if perms.ip %}<li><a class="postip" href="https://nic.ru/whois/?searchWord={{ post.ip }}" title="Проверить IP-адрес">IP</a></li>{% endif %}
{% if premod_mode %}
<li><a class="modaccept ajax" href="{{ url('moderate/'~forum.hurl~'/accept.htm?id='~post.id~'&authkey='~accept_key) }}" title="Допустить"><i class="fas fa-check-circle"></i></a></li>
<li><a class="postedit" href="{{ url(post.full_hurl) }}edit.htm?id={{ post.id }}" title="Редактировать"><i class="fas fa-pencil-alt"></i></a></li>
<li><a class="postdelete ajax" href="{{ url('moderate/'~forum.hurl~'/delete_post.htm?id='~post.id~'&authkey='~delete_key) }}" title="Удалить"><i class="far fa-trash-alt"></i></a></li>
{% elseif trashbox_mode %}
<li><a class="modaccept ajax" href="{{ url('moderate/'~forum.hurl~'/accept.htm?id='~post.id~'&authkey='~accept_key) }}" title="Восстановить"><i class="fas fa-undo"></i></a></li>
{% else %}

{% if perms.post %}<li><a class="postquote" href="{{ url(topic.full_hurl) }}reply.htm?quote={{ post.id }}" title="Цитировать"><i class="fas fa-quote-left"></i></a></li>{% endif %}
{% if post.editable %}<li><a class="postedit" href="edit.htm?id={{ post.id }}" title="Редактировать"><i class="fas fa-pencil-alt"></i></a></li>{% endif %}
{% if is_moderator %}
{% if not mod_no_marks %}
{% if post.marked %}
<li><a class="postmark" href="{{ url('moderate/'~topic.full_hurl~'mark_post.htm?id='~post.id~'&unmark=1') }}" title="Снять пометку"><i class="far fa-minus-square"></i></a></li>
{% else %}
<li><a class="postmark" href="{{ url('moderate/'~topic.full_hurl~'mark_post.htm?id='~post.id) }}" title="Пометить для модераторских дейсвтий"><i class="far fa-plus-square"></i></a></li>
{% endif %}{% endif %}

<li><a class="postdelete ajax" href="{{ url('moderate/'~topic.full_hurl~'delete_post.htm?id='~post.id~'&authkey='~delete_key) }}" title="Удалить"><i class="far fa-trash-alt"></i></a></li>
{% endif %}

{% endif %}
</ul>