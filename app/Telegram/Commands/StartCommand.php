<?php


namespace App\Telegram\Commands;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\ELeader;
use App\Telegram\UpdateHandlers\eLeader\BotELeaderCallbackHandler;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;
use Telegram\Bot\Api;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramOtherException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use WeStacks\TeleBot\Exceptions\TeleBotException;

class StartCommand extends Command
{
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Start Command to get you started";

    /**
     * @inheritdoc
     */
    public function handle()
    {
        try {
            if (!is_null(BotUser::query()->firstWhere("chat_id", "=", $this->update->message->chat->id))) {
                $this->welcome_message($this->update);
            } else {
                $bot_user = BotUser::query()->create(
                    [
                        'chat_id' => $this->update->message->chat->id,
                        'first_name' => $this->update->message->from->first_name,
                        'last_name' => $this->update->message->from->last_name ?? null,
                        'username' => $this->update->message->from->username ?? null,
                        'telegram_user_id' => $this->update->message->from->id,
                        'is_bot' => $this->update->message->from->is_bot,
                    ]
                );

                BotStatus::query()->create(
                    [
                        'user_id' => $bot_user->id,
                        'last_question' => '',
                        'last_answer' => '',
                        'path' => '',
                        'back_path' => 'root',
                        'root_path' => 'root',
                    ]
                );

                $this->welcome_message($this->update);
            }

        } catch (TelegramOtherException $e) {
            Log::error('Line: ' . $e->getLine() . ' File:' . $e->getFile() . 'Message: ' . $e->getMessage());
        }
    }

    private function welcome_message($update)
    {
        $bot = new Api();
        if (isset($update->callback_query)) {
            $message = $this->update->callback_query->message;
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->callback_query->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
            try {
                (new BotELeaderCallbackHandler())->request_phone_number($bot, $bot_user, $bot_status, $message, $update);
            } catch (TelegramSDKException $e) {
                Log::debug($e->getMessage());
            }

        } elseif (!is_null($update->message)) {
            $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->message->chat->id);
            $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

            try {
                (new BotELeaderCallbackHandler())->request_phone_number($bot, $bot_user, $bot_status, $update->message, $update);
            } catch (TelegramSDKException $e) {
                Log::debug($e->getMessage());
            } catch (TeleBotException $e) {
                Log::debug($e->getMessage());
            }
        }

//        // This will send a message using `sendMessage` method behind the scenes to
//        // the user/chat id who triggered this command.
//        // `replyWith<Message|Photo|Audio|Video|Voice|Document|Sticker|Location|ChatAction>()` all the available methods are dynamically
//        // handled when you replace `send<Method>` with `replyWith` and use the same parameters - except chat_id does NOT need to be included in the array.
//        $this->replyWithMessage(['text' => 'Hello! Welcome to our bot, Here are our available commands:']);
//
//        // This will update the chat status to typing...
//        $this->replyWithChatAction(['action' => Actions::TYPING]);
//
//        // This will prepare a list of available commands and send the user.
//        // First, Get an array of all registered commands
//        // They'll be in 'command-name' => 'Command Handler Class' format.
//        $commands = $this->getTelegram()->getCommands();
//
//        // Build the list
//        $response = '';
//        foreach ($commands as $name => $command) {
//            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
//        }
//
//        // Reply with the commands list
//        $this->replyWithMessage(['text' => $response]);

        // Trigger another command dynamically from within this command
        // When you want to chain multiple commands within one or process the request further.
        // The method supports second parameter arguments which you can optionally pass, By default
        // it'll pass the same arguments that are received for this command originally.
////        $this->triggerCommand('subscribe');
    }
}
