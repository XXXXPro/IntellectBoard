{% import 'macro.tpl' as macros %}
  <fieldset><legend>Аватар и фото</legend>
    <div><label><span>Аватар пользователя<small>Допускаются файлы в форматах JPEG, GIF, PNG, BMP.<br />
Максимальный размер: {{ get_opt('userlib_avatar_x') }}&times;{{ get_opt('userlib_avatar_y') }}<br />
Если файл больше максимально допустимого размера, он будет автоматически уменьшен.</small></span>
<span id="avatar_content">{{ macros.avatar(formdata.basic.id,formdata.basic.avatar,'Аватар пользователя -- это маленькая картинка, отображаемая рядом с сообщением') }}<input type="file" name="avatar" size="24" /> </span></label>
<label><input type="checkbox" name="basic[avatar_delete]" value="1" /> Удалить аватар</label>
      </div>
    <div><label><span>Главная фотография пользователя</span>
<span id="photo_content">{{ macros.photo(formdata.basic.id,formdata.basic.photo,'Главная фотография пользователя') }} <input type="file" name="photo" size="24" onchange="change_img(this)"/></span></label>
<label><input type="checkbox" name="basic[photo_delete]" value="1" /> Удалить фотографию</label>
    </div>
  </fieldset>

