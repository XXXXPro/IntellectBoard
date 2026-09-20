<div class="prating">
{% if not post.norate %}<a href="rate.htm?p={{ post.id }}&amp;d=pro"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4"><path d="M7 10v12"></path><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path></svg></a>
{% elseif post.rating_value=="1" %}<a href="unrate.htm?p={{ post.id }}"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4"><path d="M7 10v12"></path><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path></svg></a>
{% else %}<span class="norate" title="{{ post.norate }}"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-thumbs-up h-4 w-4"><path d="M7 10v12"></path><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"></path></svg></span>
{% endif %}
<span class="prvalue"> {{ post.rating }} </span>
{% if forum.rate!=2 %}
{% if not post.norate %}<a href="rate.htm?p={{ post.id }}&amp;d=contra"><i class="far fa-thumbs-down"></i></a>
{% elseif post.rating_value=="-1" %}<a href="unrate.htm?p={{ post.id }}"><i class="fas fa-thumbs-down"></i></a>
{% else %}<span class="norate" title="{{ post.norate }}"><i class="far fa-thumbs-down"></i></span>
{% endif %}{% endif %}
</div>