<?php


namespace App\Telegram\UpdateHandlers\eLeader;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\ELeader;
use App\Traits\TelegramCustomTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class BotELeaderCallbackHandler
{
    use TelegramCustomTrait;

    /**
     * @param TeleBot $bot
     * @param Message $message
     */
    public function customer_service_contact(TeleBot $bot, Message $message)
    {
        $customerServiceContactMessage = 'ውድ ደንበኛችን የቢ.ጂ.አይ ኢትዮጵያ ደንበኛ አገልግሎትን ለማግኘት የሚከተሉትን ስልክ ቁጥሮች መጠቀም ይችላሉ።'.chr(10).chr(10).
            '📞 +251948058656'.chr(10).
            '📞 +251948058657'.chr(10).
            '📞 +251115181515'.chr(10).
            '📞 +251115181474';

        $bot->sendMessage([
            'chat_id'=>$message->chat->id,
            'text'=>$customerServiceContactMessage,
            'parse_mode'=>'markdown',
        ]);
    }

    /**
     * @param TeleBot $bot
     * @param Builder|Model $bot_user
     * @param Message $message
     */
    public function visit_info(TeleBot $bot, BotUser $bot_user, Message $message)
    {
        $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
        $visitData = collect(DB::connection('eLeader')->select("select _tbEleaderExportActivitiesProductTasks.ActivityID, _tbEleaderExportActivitiesProductTaskDetails.ProductID, FORMAT(_tbEleaderExportActivitiesProductTasks.StopActivity,'MMMM dd, yyyy') as visit_date, _tbEleaderExportProducts.ProductName as product_name, _tbEleaderExportActivitiesProductTaskDetails.FieldName as measure, _tbEleaderExportActivitiesProductTaskDetails.FieldValue as quantity from _tbEleaderExportActivitiesProductTasks left join _tbEleaderExportActivitiesProductTaskDetails on _tbEleaderExportActivitiesProductTaskDetails.ActivityID = _tbEleaderExportActivitiesProductTasks.ActivityID left join _tbEleaderExportProducts on _tbEleaderExportProducts.ProductID = _tbEleaderExportActivitiesProductTaskDetails.ProductID where _tbEleaderExportActivitiesProductTasks.ObjectID = '02121000000000001403673' and _tbEleaderExportActivitiesProductTaskDetails.FieldName = 'Quantity pcs' and _tbEleaderExportActivitiesProductTaskDetails.TaskStatus = 'executed' order by visit_date desc"));
        $visitDates = $visitData->pluck('visit_date')->unique();
        $visitMessage = '';
        foreach ($visitDates->take(3) as $visitDate) {
            $visitMessage .= '*Date: ' . $visitDate . '*' . chr(10);
            foreach ($visitData->where('visit_date', '=', $visitDate) as $item) {
                $visitMessage .= $item->product_name . ' (' . $item->measure . ') = ' . $item->quantity . chr(10);
            }
            $visitMessage .= chr(10);
        }

        $bot->sendMessage([
            'chat_id' => $message->chat->id,
            'text' => $visitMessage,
            'parse_mode' => 'markdown',
        ]);

    }

    /**
     * @param TeleBot $bot
     * @param Builder|Model $bot_user
     * @param Message $message
     * @param Update $update
     */
    public function send_client_info(TeleBot $bot, BotUser $bot_user, Message $message, Update $update)
    {
        $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));

        if ($object = $eLeaderUserData->firstWhere('ObjectID', '!=', null)) {
            $clientInfo = collect(DB::connection('eLeader')->select("select top (1000) ObjectID,FieldCode,FieldName,FieldValue from _tbEleaderExportObjectParameters where ObjectID = '" . $object->ObjectID . "' and ( FieldName = 'Type' or FieldName = 'Territory' or FieldName = 'Tin Number' or FieldName = 'Route' or FieldName = 'BGI ID' or FieldName = 'Bottle Distributor' or FieldName = 'Outlet Tag' or FieldName = 'Machine Cleaning contact person' or FieldName = 'Customer Service' ) and ( FieldCode = 'OBJ_PARAM_TYPE' or FieldCode = 'OBJ_PARAM_TIN' or FieldCode = 'OBJ_PARAM_MCCP' or FieldCode = 'OBJ_PARAM_Territory' or FieldCode = 'OBJ_PARAM_Route' or FieldCode = 'OBJ_PARAM_BotlDsitr' or FieldCode = 'OBJ_PARAM_BGIID' or FieldCode = 'OBJ_PARAM_Outlet_Tag' )"));
            $clientLocation = collect(DB::connection('eLeader')->select("select ObjectID, ObjectName, City, Country from _tbEleaderExportObjects where ObjectID = '" . $object->ObjectID . "'"))->first();
            $clientInfoMessage = '✔️ Name: ' . $clientLocation->ObjectName . chr(10) . '✔️ Location: ' . $clientLocation->City . ', ' . $clientLocation->Country . chr(10);
            foreach ($clientInfo as $info) {
                $clientInfoMessage .= '✔️ ' . $info->FieldName . ': ' . $info->FieldValue . chr(10);
            }

            $bot->sendMessage([
                'chat_id' => $message->chat->id,
                'text' => $clientInfoMessage,
            ]);
        } else {
            $this->not_registered_to_bgi_betegna($bot, $update, 'amharic');
        }
    }

    /**
     * @param Api $bot
     * @param BotUser $bot_user
     * @param BotStatus $bot_status
     * @param Message $message
     * @param Update $update
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function request_phone_number(Api $bot, BotUser $bot_user, BotStatus $bot_status, Message $message, Update $update)
    {
        if (ELeader::query()->where('user_id', '=', $bot_user->id)->doesntExist()) {
            $bot_status->update([
                'path' => 'eLeader',
                'last_question' => 'eLeader_phone_number_request',
                'last_answer' => '',
                'back_path' => 'root',
            ]);

            $bot->sendPhoto([
                'chat_id' => $message->chat->id,
                'photo' => env('TELEGRAM_BGI_BETEGNA_PHOTO_FILE_ID'),
                'caption' => 'ሰላም ውድ የቦታችን ተጠቃሚ' . chr(10) .
                    'ይህ የቢ.ጂ.አይ ቤተኛ ቴሌግራም ቦት ነው።' . chr(10) .
                    'እባክዎን የእርሶ ቤት የተመዘገበትን ስልክ ቁጥር ይላኩልን።' . chr(10) .
                    'ስልክዎን ሲያስገቡ 09 ብሎ እንዲጀምር ያድርጉት። ለምሳሌ፡ 0900110011',
            ]);
        } else {
            (new BotELeaderUpdateHandler())->eLeader_starting_menu($update, $bot_status, $bot);
        }
    }

    /**
     * @param TeleBot $bot
     * @param Builder|Model $bot_user
     * @param Message $message
     */
    public function send_enqu_amount(TeleBot $bot, $bot_user, Message $message)
    {
        $eLeaderUserData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
        $enquMessage = '';

        if ($eLeaderUserData->isNotEmpty()) {
            $enquData = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where ObjectID='" . $eLeaderUserData->first()->ObjectID . "' and (FieldCode='OBJ_PARAM_EarnedPoints' or FieldCode='OBJ_PARAM_Fidelity_ID' or FieldCode='OBJ_PARAM_BGIID') and (FieldName ='Earned points' or FieldName='Fidelity ID' or FieldName='BGI ID')"));
            if ($enqu = $enquData->where('FieldName', '=', 'Earned points')->first()) {
                $enquMessage .= 'ያለዎት የእንቁ ብዛት ' . $enqu->FieldValue . ' 💎 ነው።';
            } else {
                $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት አልተመዘገቡም።' . chr(10) . 'ለመመዝገብ ፕሮሞተርዎን ያነጋግሩ።';
            }
        } else {
            $enquMessage .= 'ውድ ደንበኛችን የቢ.ጂ.አይ ቤተኛ አገልግሎት ተጠቃሚዎች ዝርዝር ውስጥ አላገኘንዎትም።';
        }
;
        $bot->sendMessage([
            'chat_id' => $message->chat->id,
            'text' => $enquMessage,
        ]);
    }

}
