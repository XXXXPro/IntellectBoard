<?php

/** ================================
 *  @package IntbPro
 *  @author 4X_Pro <me@4xpro.ru>
 *  @version 3.06
 *  @url https://intbpro.ru
 *  HTML tag closer helper library
 *  Written with AI assistance
 *  ================================ **/

class Library_tagcloser {
    public static function fix_unclosed($html) {
        $stack = [];
        $position = 0;
        $length = strlen($html);
        
        while ($position < $length) {
            // Ищем открывающий тег
            if ($html[$position] === '<') {
                $tagStart = $position;
                
                // Пропускаем комментарии и CDATA
                if (substr($html, $position, 4) === '<!--') {
                    $position = strpos($html, '-->', $position);
                    if ($position === false) break;
                    $position += 3;
                    continue;
                }
                
                if (substr($html, $position, 9) === '<![CDATA[') {
                    $position = strpos($html, ']]>', $position);
                    if ($position === false) break;
                    $position += 3;
                    continue;
                }
                
                // Обрабатываем закрывающие теги
                if ($position + 1 < $length && $html[$position + 1] === '/') {
                    $position += 2;
                    $tagEnd = strpos($html, '>', $position);
                    if ($tagEnd === false) break;
                    
                    $tagName = substr($html, $position, $tagEnd - $position);
                    $tagName = strtolower(preg_replace('/\s.*/', '', $tagName));
                    
                    // Убираем соответствующий открывающий тег из стека
                    $found = false;
                    for ($i = count($stack) - 1; $i >= 0; $i--) {
                        if ($stack[$i] === $tagName) {
                            array_splice($stack, $i, 1);
                            $found = true;
                            break;
                        }
                    }
                    
                    $position = $tagEnd + 1;
                    continue;
                }
                
                // Обрабатываем самозакрывающиеся теги
                $isSelfClosing = false;
                $tempPos = $position;
                while ($tempPos < $length && $html[$tempPos] !== '>') {
                    if ($html[$tempPos] === '/' && 
                        $tempPos + 1 < $length && 
                        $html[$tempPos + 1] === '>') {
                        $isSelfClosing = true;
                        break;
                    }
                    $tempPos++;
                }
                
                // Извлекаем имя тега
                $tagEnd = strpos($html, '>', $position);
                if ($tagEnd === false) break;
                
                $tagContent = substr($html, $position + 1, $tagEnd - $position - 1);
                $tagContent = trim($tagContent);
                
                // Удаляем атрибуты и параметры
                $tagName = preg_split('/\s+/', $tagContent)[0];
                $tagName = strtolower($tagName);
                
                // Игнорируем самозакрывающиеся и специальные теги
                $selfClosingTags = [
                    'area', 'base', 'br', 'col', 'embed', 'hr', 
                    'img', 'input', 'link', 'meta', 'param', 
                    'source', 'track', 'wbr', '!doctype', '?xml'
                ];
                
                if (!$isSelfClosing && !in_array($tagName, $selfClosingTags)) {
                    array_push($stack, $tagName);
                }
                
                $position = $tagEnd + 1;
            } else {
                $position++;
            }
        }
        
        // Добавляем недостающие закрывающие теги
        $result = $html;
        while (!empty($stack)) {
            $tag = array_pop($stack);
            $result .= "</$tag>";
        }
        
        return $result;
    }
}