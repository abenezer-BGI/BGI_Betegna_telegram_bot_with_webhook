<?php

namespace App\Traits;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

trait TelegramCustomTrait
{

    /**
     * To append the current path to the DB's already existing path column value
     * @param $path
     * @param $text
     * @return string
     */
    public function path_append($path, $text)
    {
        $array_path = explode('.', $path);
        return end($array_path) === $text ? $path : $path . $text;
    }

    /**
     * Reply to the bot user with an error message
     * @param Api $bot
     * @param Update $update
     * @param string $language
     * @throws TelegramSDKException
     */
    public function error_message(Api $bot, Update $update, $language)
    {
        if ($language === 'amharic') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ያስገቡት መልእክት ልክ አይደለም!',
            ]);
        } elseif ($language === 'english') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'The reply you sent is not correct!',
            ]);
        }
    }

    /**
     * Reply with BGI Betegna error message
     * @param Api $bot
     * @param Update $update
     * @param string $language
     * @throws TelegramSDKException
     */
    public function not_registered_to_bgi_betegna(Api $bot, Update $update, $language)
    {
        if ($language === 'amharic') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት ተጠቃሚዎች ዝርዝር ውስጥ አላገኘንዎትም።' . chr(10) . 'ለመመዝገብ ፕሮሞተሮን ያነጋግሩ።',
            ]);
        } elseif ($language === 'english') {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'Deer esteemed customer, we couldn\'t find you in the BGI Betegna subscribers list.' . chr(10) . 'To subscribe to the service please contact your promoter.',
            ]);
        }
    }
}
