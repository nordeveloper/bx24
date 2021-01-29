<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\Petrol;
use app\models\Bid;
use app\models\Company;
use app\models\Fuel;
use app\models\History;
use app\models\Receiver;
use app\models\Sender;
use app\models\StationNetwork;
use app\models\Stations;
use app\models\Subcompany;
use app\models\SumFuelsStations;
use app\models\UsersInfo;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;


$_SERVER["DOCUMENT_ROOT"] = '/home/bitrix/www';

define('NEED_AUTH',false);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('CHK_EVENT', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{

    private $checkAM = '5db99765e2019423d6e0df83';
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    // array search anel kam foreachov idner@ stugem ete chka vabshe jnjem
    public function actionIndex()
    {
        $getApi = new Petrol();
        $response = $getApi->getUsersHistoryCharging();        

        if($response){

            $count = 0;

            foreach ($response as $res ) {

                $arDeal = [];
                if ($checkExist = History::find()->where(['history_id' => $res->id])->one()) {
                    $history = $checkExist;
                } else {
                    $history = new History();
                }
                $history->history_id = $res->id;
                $history->transaction_number = $res->transactionNumber;
                $arDeal['transaction_number'] = $res->transactionNumber;
                $history->type = $res->type;
                $arDeal['type'] =  $res->type;
                
                if($res->type=='SPENT_COMPANY_FUEL' || $res->type=='SPENT_PERSONAL_FUEL' ){
                    $arDeal['type_id'] = 'SALE';
                    $arDeal['title'] = "Գնում".' '.$res->transactionNumber;
                }else{
                    $arDeal['type_id'] = 'COMPLEX';
                    $arDeal['title'] = "Լիցքավորում".' '.$res->transactionNumber;
                }

                
                if ( isset($res->sender) ) {
                    $history->sender_id = $res->sender->id;
                    if ($sender = Sender::find()->where(['sender_id' => $res->sender->id])->one()) {
                        $sender->sender_id = $res->sender->id;
                        $arDeal['sender_name'] =  $res->sender->username;
                        $sender->user_name = $res->sender->username;
                        $sender->first_name = $res->sender->firstName;
                        $sender->last_name = $res->sender->lastName;
                        $sender->account_number = $res->sender->accountNumber;
                        $sender->save();
                    } else {
                        $sender = new Sender();
                        $sender->sender_id = $res->sender->id;
                        $sender->user_name = $res->sender->username;
                        $sender->first_name = $res->sender->firstName;
                        $sender->last_name = $res->sender->lastName;
                        $sender->account_number = $res->sender->accountNumber;
                        $sender->save();
                    }
                }
                if (isset($res->receiver)) {
                    if ($receiver = Receiver::find()->where(['receiver_id' => $res->receiver->id])->one()) {
                        $receiver->receiver_id = $res->receiver->id;
                        $receiver->user_name = $res->receiver->username;
                        $arDeal['receiver_name']=$res->receiver->username;
                        $receiver->first_name = $res->receiver->firstName;
                        $receiver->last_name = $res->receiver->lastName;
                        $receiver->account_number = $res->receiver->accountNumber;
                        $receiver->save();
                    } else {
                        $receiver = new Receiver();
                        $receiver->receiver_id = $res->receiver->id;
                        $receiver->user_name = $res->receiver->username;
                        $receiver->first_name = $res->receiver->firstName;
                        $receiver->last_name = $res->receiver->lastName;
                        $receiver->account_number = $res->receiver->accountNumber;
                        $receiver->save();
                    }
                }
    
                if (isset($res->fuel)) {
                    $history->fuel_id = $res->fuel->id;
                    $title = '';
                    $shortName = '';
                    for ($i = 0 ; $i < count($res->fuel->translations) ; $i++) {
                        if ($res->fuel->translations[$i]->language == $this->checkAM) {
                            $title = $res->fuel->translations[$i]->title ;
                            $shortName = $res->fuel->translations[$i]->shortName ;
                        }
                    }
                    if ($fuel = Fuel::find()->where(['fuel_id' => $res->fuel->id])->one()) {
                        $fuel->fuel_id = $res->fuel->id;
                        $fuel->title = $title;
                        $arDeal['fuel_title'] =$title;
                        $arDeal['fuel_id'] = $res->fuel->id;
                        $fuel->short_name = $shortName;
                        $fuel->color = $res->fuel->color;
                        $fuel->price = $res->fuel->price;
                        $fuel->market_price = $res->fuel->marketPrice;
                        $fuel->unit = $res->fuel->measurementUnit;
                        $fuel->save();
                    } else {
                        $fuel = new Fuel();
                        $fuel->fuel_id = $res->fuel->id;
                        $fuel->title = $title;
                        $fuel->short_name = $shortName;
                        $fuel->color = $res->fuel->color;
                        $fuel->price = $res->fuel->price;
                        $fuel->market_price = $res->fuel->marketPrice;
                        $fuel->unit = $res->fuel->measurementUnit;
                        $fuel->save();
                    }
    
                }
    
                if (isset($res->bid)) {
                    $history->bid_id = $res->bid->id;
                    if ($bid = Bid::find()->where(['bid_id' => $res->bid->id])->one()) {
                        $bid->bid_id = $res->bid->id;
                        $bid->register_car_number = $res->bid->registeredCarNumber;
                        $bid->filled_car_number = $res->bid->filledCarNumber;
                        $bid->status = $res->bid->status;
                        $bid->save();
                    } else {
                        $bid = new Bid();
                        $bid->bid_id = $res->bid->id;
                        $bid->register_car_number = $res->bid->registeredCarNumber;
                        $bid->filled_car_number = $res->bid->filledCarNumber;
                        $bid->status = $res->bid->status;
                        $bid->save();
                    }
                }
    
                if (isset($res->company)) {
                    $history->company_id = $res->company->id;
                    if ($company = Company::find()->where(['company_id' => $res->company->id])->one()) {
                        $company->company_id = $res->company->id;
                        $company->user_name = $res->company->username;
                        $arDeal['company_user_name'] = $res->company->username;
                        $company->phone_number1 = $res->company->phoneNumber1;
                        $company->phone_number2 = $res->company->phoneNumber2;
                        $company->company_name = $res->company->translations[0]->name;
                        $company->company_marketing_name = $res->company->translations[0]->marketingName;
                        $company->isHidden = $res->company->isHidden == true ?  1 : 0;
                        $company->save();
                    } else {
                        $company = new Company();
                        $company->company_id = $res->company->id;
                        $company->user_name = $res->company->username;
                        $company->phone_number1 = $res->company->phoneNumber1;
                        $company->phone_number2 = $res->company->phoneNumber2;
                        $company->company_name = $res->company->translations[0]->name;
                        $company->company_marketing_name = $res->company->translations[0]->marketingName;
                        $company->isHidden = $res->company->isHidden == true ?  1 : 0;
                        $company->save();
                    }
                }
    
                if (isset($res->station)) {
                    $history->station_id = $res->station->id;
                }
    
                if (isset($res->subCompany)) {

                    $history->station_id = $res->station->id;
                    if ($subCompany = Subcompany::find()->where(['subCompany_id' => $res->subCompany->id])->one()) {
                        $subCompany->subCompany_id = $res->subCompany->id;
                        $subCompany->user_name = $res->subCompany->username;
                        $subCompany->phone_number1 = $res->subCompany->phoneNumber1;
                        $subCompany->phone_number2 = $res->subCompany->phoneNumber2;
                        $subCompany->subCompany_name = $res->subCompany->translations[0]->name;
                        $subCompany->subCompany_marketing_name = $res->subCompany->translations[0]->marketingName;
                        $subCompany->isHidden = $res->subCompany->isHidden == true ?  1 : 0;
                        $subCompany->save();
                    } else {
                        $subCompany = new Subcompany();
                        $subCompany->subCompany_id = $res->subCompany->id;
                        $subCompany->user_name = $res->subCompany->username;
                        $subCompany->phone_number1 = $res->subCompany->phoneNumber1;
                        $subCompany->phone_number2 = $res->subCompany->phoneNumber2;
                        $subCompany->subCompany_name = $res->subCompany->translations[0]->name;
                        $subCompany->subCompany_marketing_name = $res->subCompany->translations[0]->marketingName;
                        $subCompany->isHidden = $res->subCompany->isHidden == true ?  1 : 0;
                        $subCompany->save();
                    }
                }

                $history->amount = $res->amount;
                $history->sum = $res->sum;
                $arDeal['amount'] = $res->amount;
                $arDeal['sum'] = $res->sum;
                $history->sold_price = ($res->sum) / ($res->amount);
                // echo $res->date."\n";

                $history->date = date("Y-m-d H:i:s", strtotime($res->date));
                // echo "\n";
                // echo $history->date."\n";
                $arDeal['date'] = date("d.m.Y H:i:s", strtotime($res->date));
    
                $arDeal['api_id'] = $res->id;
    
                if($res->company->id){
                    $arDeal['company_id'] = $this->getCompanyByIdentificator($res->company->id);
                }
    
   
                if($res->receiver->id){
                    $arDeal['contact_id'] = $this->getContactByIdentificator($res->receiver->id);
                    // echo 'FindEd Contact '.$arDeal['contact_id']."\n";
                }
    
                $deal_id = $this->getDealByIdentificator($res->id);

                if( !$deal_id ){
                    $this->AddDealToCrm($arDeal);
                }
    
                
                $history->save();
            }
        }else{        
        echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }
       
    }

    // array search anel kam foreachov idner@ stugem ete chka vabshe jnjem
    public function actionStation()
    {
        $getApi = new Petrol();
        $response = $getApi->getStations();
        if ($response) {
            foreach ($response as $res ) {
                for ($i = 0 ; $i < count($res) ; $i++) {
                    $station = Stations::findOne(['id' => $res[$i]->id]);
                    if ($station) {
                        $station->id = $res[$i]->id;

                        $station->type = $res[$i]->type;
                        $station->station_network_id = $res[$i]->stationNetwork->id;
                        $station->last_update = date("Y-m-d h:i:s", strtotime($res[$i]->lastUpdatedAt));
                        $station->title = $res[$i]->translations[0]->title;
                        $station->supervisor =  $res[$i]->translations[0]->supervisor;
                        $station->isHidden = $res[$i]->isHidden == true ?  1 : 0;
                        $station->save();
                    } else {
                        $station = new Stations();
                        $station->id = $res[$i]->id;
                        $station->type = $res[$i]->type;
                        $station->station_network_id = $res[$i]->stationNetwork->id;
                        $station->last_update = date("Y-m-d h:i:s", strtotime($res[$i]->lastUpdatedAt));
                        $station->title = $res[$i]->translations[0]->title;
                        $station->supervisor =  $res[$i]->translations[0]->supervisor;
                        $station->isHidden = $res[$i]->isHidden == true ?  1 : 0;
                        $station->save();
                    }
                }
            }
        }else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }


    }


    public function actionGetStationNetFuels () {
        $getApi = new Petrol();
        $response = $getApi->getStationNetFuelsSum();
        if ($response) {
            foreach ($response as $res) {
                $stationById = SumFuelsStations::find()->where(['station_network_id' => $res['id']])->one();
                $refuel = $res['sum']->refueledFuels[0]->fuelId;
                $sold = $res['sum']->soldFuels[0]->fuelId;
                if (($refuel && $sold) && $sold == $refuel) {
                    if ($stationById) {
                        $stationById->fuel_id = $refuel;
                        $stationById->refueledFuel = $res['sum']->refueledFuels[0]->sumAmounts;
                        $stationById->soldFuel = $res['sum']->soldFuels[0]->sumAmounts;
                        $stationById->sum = $res['sum']->soldFuels[0]->sumAmounts - $res['sum']->refueledFuels[0]->sumAmounts;
                        $stationById->save();
                    } else {
                        $stationById = new SumFuelsStations();
                        $stationById->station_network_id = $res['id'];
                        $stationById->fuel_id = $refuel;
                        $stationById->refueledFuel = $res['sum']->refueledFuels[0]->sumAmounts;
                        $stationById->soldFuel = $res['sum']->soldFuels[0]->sumAmounts;
                        $stationById->sum = $res['sum']->soldFuels[0]->sumAmounts - $res['sum']->refueledFuels[0]->sumAmounts;;
                        $stationById->save();
                    }
                }
            }
        }
        else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }
  
    }

    // array search anel kam foreachov idner@ stugem ete chka vabshe jnjem
    public function actionStationNetwork()
    {
        $getApi = new Petrol();
        $response = $getApi->getStationNetworks();

        if ($response) {
            foreach ($response as $res ) {
               
                if ($stationNetwork = StationNetwork::find()->where(['id' => $res->id])->one()) {
                    $stationNetwork->id = $res->id;
                    $stationNetwork->phone_number = $res->phoneNumber;
                    $stationNetwork->title = $res->translations[0]->title;
                    $stationNetwork->legal_address = $res->translations[0]->legalAddress;
                    $stationNetwork->supervisor =  $res->translations[0]->supervisor;
                    $stationNetwork->isHidden = $res->isHidden == true ?  1 : 0;
                    $stationNetwork->amount = 10000;
                    $stationNetwork->percent = 60;
                    $stationNetwork->type = $res->type;
                    $stationNetwork->save();

                } else {
                    
                    $arCompany = [];
                    $arCompany['title'] = $res->translations[0]->title;
                    $arCompany['phone'] = $res->phoneNumber;
                    $arCompany['type'] = 'PARTNER';
                    $arCompany['taxCode'] = 1;

                    $this->AddCompanyToCrm($arCompany);

                    $stationNetwork = new StationNetwork();
                    $stationNetwork->id = $res->id;
                    $stationNetwork->phone_number = $res->phoneNumber;
                    $stationNetwork->title = $res->translations[0]->title;
                    $stationNetwork->legal_address = $res->translations[0]->legalAddress;
                    $stationNetwork->supervisor =  $res->translations[0]->supervisor;
                    $stationNetwork->isHidden = $res->isHidden == true ?  1 : 0;
                    $stationNetwork->amount = 10000;
                    $stationNetwork->percent = 60;
                    $stationNetwork->type = $res->type;
                    $stationNetwork->save();
                }
            }
        }else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }

    }


    public function actionFuels()
    {
        $getApi = new Petrol();
        $response = $getApi->getFuels();

        if ($response) {
            foreach ($response->items as $fuels) {

                $fuelsUpdate = Fuel::find()->where(['fuel_id' => $fuels->id])->one();
                if ($fuelsUpdate) {
                    $fuelsUpdate->title = $fuels->translations[0]->title;
                    $fuelsUpdate->color = $fuels->color;
                    $fuelsUpdate->price = $fuels->price;
                    $fuelsUpdate->market_price = $fuels->marketPrice;
                    $fuelsUpdate->unit = $fuels->measurementUnit;
                    $fuelsUpdate->save();
                } else {
                    $fuelsCreate = new Fuel();
                    $fuelsCreate->fuel_id = $fuels->id;
                    $fuelsCreate->title = $fuels->translations[0]->title;
                    $fuelsCreate->color = $fuels->color;
                    $fuelsCreate->price = $fuels->price;
                    $fuelsCreate->market_price = $fuels->marketPrice;
                    $fuelsCreate->unit = $fuels->measurementUnit;
                    $fuelsCreate->save();
                }
            }

        }else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }

    }

    public function actionUsers () {

        $getApi = new Petrol();
        $response = $getApi->getUsers();

        if ($response) {

            $count = 0;

            foreach ($response as $res ) {

                $usersInfo = UsersInfo::find()->where(['id' => $res->id])->one();

                if ( !is_null($usersInfo) ) {

                    $updateLastUpdate = $getApi->getUserById($res->id);
                    $usersInfo->id = $res->id;
                    $usersInfo->username = $res->username;
                    $usersInfo->firstname = $res->firstName;
                    $usersInfo->lastname = $res->lastName;
                    $usersInfo->isBlocked = $res->isBlocked == false ? 0 : 1;
                    $usersInfo->createdAt = date('Y-m-d h:i:s' , strtotime($res->createdAt));
                    $usersInfo->updateAt = date('Y-m-d h:i:s' , strtotime($updateLastUpdate->lastUpdatedAt));
                    $usersInfo->company = !empty($res->companyBalances)  ? $res->companyBalances[0]->id : 0;
                    $usersInfo->save(false);


                    $leadID = $this->FindLeadIDByPhone($res->username);

                    $contactID = $this->getContactByIdentificator($res->id);

                    if(!empty($leadID) and !empty($contactID) ){
                        
                        $sc = $this->FromLeadContact($leadID, $contactID);

                        if( $sc===false ){

                            // echo 'FromLeadContac False'."\n";
                            // echo 'ContactID:'.$contactID."\n";
                            // echo 'LeadID:'.$leadID."\n";

                            $arDeal = array();
                            $arDeal['stage_id'] = 'C3:NEW';
                            // $arDeal['contact_id']= $contactID;
                            $arDeal['category_id'] = 3;
                            $dID = $this->AddDealToCrm($arDeal);
                            // echo 'Deal ID:'.$dID."\n";

                            $arLead['contact_id'] = $contactID;
                            $arLead['status_id'] = 'CONVERTED';
                            $upL = $this->LeadUpdate($leadID, $arLead);
                            print_r($upL);

                            $arContact['lead_id'] = $leadID;
                            $upk = $this->ContactUpdateInCrm($contactID, $arContact);
                            print_r($upk);

                            $arWorkflowParameters = [];
                            $arErrorsTmp = [];

                            if(\CModule::IncludeModule("bizproc")){            
                                $workflow = \CBPDocument::StartWorkflow(
                                    18,
                                    ["crm", "CCrmDocumentDeal", 'DEAL_' . $dID],
                                    array_merge($arWorkflowParameters, ["TargetUser" => "user_1"]),
                                    $arErrorsTmp
                                );
                            }                            
                        }
                    }


                } else {

                    // adding contact to CRM contacts
                    $arContact = [];
                    $arContact['id']= $res->id;
                    $arContact['name']= $res->firstName;
                    $arContact['last_name']= $res->lastName;
                    $arContact['company_id'] = !empty($res->companyBalances)  ? $res->companyBalances[0]->id : false;
                    $arContact['phone']= $res->username;
                   
                    $arBalance = array();

                    if($res->balances){
                        foreach($res->balances as $balance){
                            if($balance->fuel->id){
                                $arBalance[$balance->fuel->id] = $balance->amount;
                            }                         
                        }
                    }

                    $arContact['balances'] = $arBalance;

                    
                    $arCBalances = array();

                    if( !empty($res->companyBalances) ){

                        foreach($res->companyBalances as $arCBalance){
                            if($arCBalance->fuel->id){
                                $arCBalances[$arCBalance->fuel->id] = $arCBalance->amount;
                            }                         
                        }
                    }                    


                    $arContact['companyBalances'] = $arCBalances;

                    $contactID = $this->AddContactToCrm($arContact);

                    $leadID = $this->FindLeadIDByPhone($res->username);
                    if(!empty($leadID) and !empty($contactID) ){
                        $this->FromLeadContact($leadID, $contactID);
                    }                    

                    $usersInfoCreate = new UsersInfo();
                    $usersInfoCreate->id = $res->id;
                    $usersInfoCreate->username = $res->username;
                    $usersInfoCreate->firstname = $res->firstName;
                    $usersInfoCreate->lastname = $res->lastName;
                    $usersInfoCreate->isBlocked = $res->isBlocked == false ? 0 : 1;
                    $usersInfoCreate->createdAt = date('Y-m-d h:i:s' , strtotime($res->createdAt));
                    $usersInfoCreate->company = !empty($res->companyBalances)  ? $res->companyBalances[0]->id : 0;
                    $usersInfoCreate->save(false);

                    // file_put_contents(__DIR__.'/../logs/CronUsersAdd.log', $res->id." ".$res->username." ".date('Y-m-d h:i:s')."\n", FILE_APPEND);
                }
            }
        }else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }

    }

    // update station-networks` percent by statistics from history of buying
    public function actionUpdatePercents () {

        $sql = "SELECT h.fuel_id as id, sn.id as nids,s.title as stationTitle, s.id as stationId,SUM(h.amount) as countFuels, sn.title as name,sn.id as stationNetId  FROM history as h
        LEFT JOIN stations as s ON s.id = h.station_id
        LEFT JOIN station_network as sn ON sn.id = s.station_network_id
        WHERE sn.title IS NOT NULL AND h.date IS NOT NULL
    	GROUP BY sn.id";
        $updateST = Yii::$app->db->createCommand($sql)
            ->queryAll();
        $genRes = 0;
        if ($updateST) {
            foreach ($updateST as $val) {
                $genRes += $val['countFuels'];
            }
            foreach ($updateST as $value) {
                $update = StationNetwork::find()->where(['id' => $value['nids']])->one();
                if ($update) {
                    $percent = $value['countFuels'] * 100 / $genRes;
                    $update->percent = round($percent);
                    if ($update->save(false)) {
                        var_dump('success');
                    } else {
                        var_dump('error');
                    }
                }
            }
        }

    }


    public function actionCompanies(){

        $arFuelType = array(
            '5db99765e2019423d6e0df84'=>'regular',
            '5db99765e2019423d6e0df85'=>'premium',
            '5db99765e2019423d6e0df86'=>'super',
            '5e353a126f7a00e3d657d313'=>'metan',
            '5db99765e2019423d6e0df87'=>'propan',
            '5e353a126f7a00e3d657d313'=>'diesel'
        );

        $getApi = new Petrol();
        $response = $getApi->getCompanies();
        $data= [];

        if ($response) {
            
            foreach($response->companies as $company){
                
                $data['id'] = $company->id;
                $data['title'] = $company->translations[0]->name;
                $data['type'] = 'CUSTOMER';
                $data['phone1'] = $company->phoneNumber1;
                $data['phone2'] = $company->phoneNumber2;
                $data['email'] = $company->email;
                $data['taxCode'] = $company->taxCode;

                if($company->balances){
                    foreach($company->balances as $balance){
                        $data['amount'][$arFuelType[$balance->fuel]] = $balance->amount;
                    }    
                }
                
                $finedid = $this->getCompanyByTaxCode($company->taxCode);

                if( empty($finedid) ){
                    
                    $this->AddCompanyToCrm($data);

                }else{

                    $arCompanies = $this->getContactByCompanyIdentificator($company->id);

                    $data['UF_CRM_1609140198'] = $arCompanies;
                    $this->CompanyUpdateInCrm($finedid, $data);
                }                
            }

        }else{
            echo $response = 'Error_curl '.date('d.m.Y H:i:s')."\n";
        }
   
    }

    
    public function AddCompanyToCrm($data){

        if(!\CModule::IncludeModule("crm")) return;

        $arFuelType = array(
            'regular'=>'UF_CRM_1609140429',
            'premium'=>'UF_CRM_1609140467',
            'super'=>'UF_CRM_1609140499',
            'diesel'=>'UF_CRM_1609140552',
            'propan'=>'UF_CRM_1609140669',
            'metan'=>'UF_CRM_1609140640'
        );

        $type = '';

        if($data['type']=='PARTNER'){
            $type ='PARTNER';
        }

        if($data['type']=='CUSTOMER'){
            $type ='CUSTOMER';
        }

        $arFields = array(
            'TITLE' => $data['title'],
            "OPENED" => "Y",
            "COMPANY_TYPE" => $type,
            'ASSIGNED_BY_ID' => 1,
            'CREATED_BY_ID' => 1,
            'UF_CRM_1609155023'=>$data['id'],
            "UF_CRM_1610523219"=>$data['taxCode']
        );

        if($data['amount']){
            $arFields[$arFuelType[key($data['amount'])]]=$data['amount'][key($data['amount'])];
        }

        if( !empty($data['phone1']) ){

            $arFields['FM']['PHONE'] = array(
                'n0' => array('VALUE' => $data['phone1'], 'VALUE_TYPE' => 'MOBILE')
            );
            $arFields["HAS_PHONE"]="Y";
        }


        if($data['phone2']){
            $arFields['FM']['PHONE'] = array(
                'n1' => array('VALUE' => $data['phone2'], 'VALUE_TYPE' => 'MOBILE')
            );
        }

        if( !empty($data['email']) ){

            $arFields['FM']['EMAIL'] = array(
                'n0' => array('VALUE' => $data['email'], 'VALUE_TYPE' => 'WORK')
            );
            $arFields["HAS_EMAIL"]="Y";
        }
        $oCompany = new \CCrmCompany(false);        

        $companyId = $oCompany->Add($arFields);
        
        if( empty($companyId) ){
            return $oCompany->LAST_ERROR;
        }
    }



    public function CompanyUpdateInCrm($id, $data){

        if( !\CModule::IncludeModule("crm") ) return;

        $arFuelType = array(
            'regular'=>'UF_CRM_1609140429',
            'premium'=>'UF_CRM_1609140467',
            'super'=>'UF_CRM_1609140499',
            'diesel'=>'UF_CRM_1609140552',
            'propan'=>'UF_CRM_1609140669',
            'metan'=>'UF_CRM_1609140640'
        );

        $arFields = array(
            'TITLE' => $data['title']        
        );

        if($data['type']=='PARTNER'){
            $arFields['COMPANY_TYPE'] ='PARTNER';
        }

        if($data['type']=='CUSTOMER'){
            $arFields['COMPANY_TYPE'] ='CUSTOMER';
        }

        if( !empty($data['id']) ){
            $arFields['UF_CRM_1609155023'] = $data['id'];
        }

        if( !empty($data['UF_CRM_1609140198']) ){

            $arFields['UF_CRM_1609140198'] = $data['UF_CRM_1609140198'];
        }
        
        if($data['amount']){
            $arFields[$arFuelType[key($data['amount'])]]=$data['amount'][key($data['amount'])];
        }

        $oCompany = new \CCrmCompany(false);
        $companyId = $oCompany->Update($id, $arFields);
        
        if( empty($companyId) ){
            return $oCompany->LAST_ERROR;
        }

    }


    public function AddDealToCrm($data) {

        if( !\CModule::IncludeModule("crm") ) return;

        $fultTypes = array(
            '5db99765e2019423d6e0df84'=>1, 
            '5db99765e2019423d6e0df85'=>2,
            'db99765e2019423d6e0df86'=>3,
            '5e353a126f7a00e3d657d313'=>4,
            '5db99765e2019423d6e0df87'=>6,
            '5db99765e2019423d6e0df88'=>5
        );

        $stage_id = "WON";

        if($data['stage_id']){
            $stage_id = $data['stage_id'];
        }

        $arFields = array(
            "COMPANY_ID" => $data['company_id'],
            "CONTACT_ID"=> $data['contact_id'],
            "OPPORTUNITY" => $data['sum'],
            "UF_CRM_1607173020" => $data['type'],
            "UF_CRM_1610353360" => $data['date'],
            "TITLE" => $data['title'],
            "STAGE_ID" => $stage_id,
            "SOURCE_ID" => "SELF",
            "CURRENCY_ID" => "AMD",
            "ASSIGNED_BY_ID" => 1,
            "TYPE_ID"=>$data['type_id'],
            "UF_CRM_1607172732" => $data['transaction_number'],
            "UF_CRM_1607172746" => $data['sender_name'],
            "UF_CRM_1609146761" => $fultTypes[$data['fuel_id']],
            "UF_CRM_1607172762" => $data['receiver_name'],
            "UF_CRM_1607172789" => $data['company_user_name'],
            "UF_CRM_1607172828" => $data['amount'],
            "UF_CRM_1610440457"=> $data['api_id']
        );

        if( !empty($data['category_id']) ){
            $arFields["CATEGORY_ID"] = $data['category_id'];
        }

        $oDeal = new \CCrmDeal(false);
        $r = $oDeal->Add($arFields);

        if ($r) {

            $arWorkflowParameters = [];
            $arErrorsTmp = [];

            if(\CModule::IncludeModule("bizproc")){
                
                $workflow = \CBPDocument::StartWorkflow(
                    18,
                    ["crm", "CCrmDocumentDeal", 'DEAL_' . $r],
                    array_merge($arWorkflowParameters, ["TargetUser" => "user_1"]),
                    $arErrorsTmp
                );
            }
            
            return $r;
            
        }else{
            return $oDeal->LAST_ERROR;
        }

    }


    public function AddContactToCrm($data){

        if(!\CModule::IncludeModule("crm")) return;

        if( !empty($data['name']) ){

            $arFields = array(
                "NAME" => $data['name'],
                "LAST_NAME" => $data['last_name'],
                "OPENED" => "N",
                "EXPORT" => "Y",
                "TYPE_ID"=>"CLIENT",
                "UF_CRM_1609154992"=>$data['id'],
                "UF_CRM_1609156218"=>$data['company_id']
            );

            $arFields["UF_CRM_1609145050"]=$data['balances']['5db99765e2019423d6e0df84'];
            $arFields["UF_CRM_1609145088"]=$data['balances']['5db99765e2019423d6e0df86'];
            $arFields["UF_CRM_1609145107"]=$data['balances']['5e353a126f7a00e3d657d313'];
            $arFields["UF_CRM_1609145141"]=$data['balances']['5db99765e2019423d6e0df88'];
            $arFields["UF_CRM_1609145164"]=$data['balances']['5db99765e2019423d6e0df87'];
            $arFields["UF_CRM_1609145208"]=$data['balances']['5db99765e2019423d6e0df85'];

            $arFields["UF_CRM_1609144858"]=$data['companyBalances']['5db99765e2019423d6e0df84'];
            $arFields["UF_CRM_1609144904"]=$data['companyBalances']['5db99765e2019423d6e0df86'];
            $arFields["UF_CRM_1609144927"]=$data['companyBalances']['5e353a126f7a00e3d657d313'];
            $arFields["UF_CRM_1609144946"]=$data['companyBalances']['5db99765e2019423d6e0df88'];
            $arFields["UF_CRM_1609144976"]=$data['companyBalances']['5db99765e2019423d6e0df87'];
            $arFields["UF_CRM_1609144882"]=$data['companyBalances']['5db99765e2019423d6e0df85'];


            if( !empty($data['phone']) ){

                $arFields['FM']['PHONE'] = array(
                    'n0' => array('VALUE' => $data['phone'], 'VALUE_TYPE' => 'MOBILE')
                );
                $arFields["HAS_PHONE"]="Y";
            }


            if( !empty($data['email']) ){

                $arFields['FM']['EMAIL'] = array(
                    'n0' => array('VALUE' => $data['email'], 'VALUE_TYPE' => 'WORK')
                );
                $arFields["HAS_EMAIL"]="Y";
            }

            $oContact = new \CCrmContact(false);
            $r = $oContact->Add($arFields, true, array('DISABLE_USER_FIELD_CHECK' => true) );
            
            if(!$r){
                $r = $oContact->LAST_ERROR;
            }

            return $r;
        }

    }    


    public function ContactUpdateInCrm($ID,$data){

        if(!\CModule::IncludeModule("crm")) return;

        // name is required
        if( $ID>0 and !empty($data['name']) ){

            $arFields = array(
                "NAME" => $data['name'],
                "LAST_NAME" => $data['last_name']
            );            

            if( !empty($data['phone']) ){
                $arFields['FM']['PHONE'] = array(
                    'n0' => array('VALUE' => $data['phone'], 'VALUE_TYPE' => 'MOBILE')
                );
                $arFields["HAS_PHONE"]="Y";
            }

            if( !empty($data['email']) ){
                $arFields['FM']['EMAIL'] = array(
                    'n0' => array('VALUE' => $data['email'], 'VALUE_TYPE' => 'WORK')
                );
                $arFields["HAS_EMAIL"]="Y";
            }

            if($data['lead_id']){
                $arFields['LEAD_ID'] = $data['lead_id'];
            }

            $oContact = new \CCrmContact(false);
            $r = $oContact->update($ID, $arFields );
            
            if(!$r){
               return $oContact->LAST_ERROR;
            }
        }

    }


    public function LeadUpdate($leadID, $arData){

        $oLead = new \CCrmLead(false);
        $arFields['CONTACT_ID'] = $arData['contact_id'];
        $arFields['STATUS_ID'] = $arData['status_id'];
        $r = $oLead->update($leadID, $arFields );
        if(!$r){
            $r = $oLead->LAST_ERROR;
        }
        return $r;        
    }


    public function getContactByIdentificator($ident){

        if(\Bitrix\Main\Loader::IncludeModule('crm')){
            global $DB;
            if(!empty($ident)){
                $dbRes = $DB->Query("SELECT * FROM b_uts_crm_contact WHERE UF_CRM_1609154992='$ident'");
    
                if( $rowCont = $dbRes->Fetch() ){
                    return $rowCont['VALUE_ID'];
                }
            }
        }
    }


    public function getContactByCompanyIdentificator($ident){

        if(\Bitrix\Main\Loader::IncludeModule('crm')){
            global $DB;
            if(!empty($ident)){
                $dbRes = $DB->Query("SELECT * FROM b_uts_crm_contact WHERE UF_CRM_1609156218='$ident'");
    
                while( $rowCont = $dbRes->Fetch() ){
                    $arContatIDS[] = $rowCont['VALUE_ID'];
                }
                return $arContatIDS;
            }
        }
    }


    public function getCompanyByTaxCode($taxtCode){

        if(\Bitrix\Main\Loader::IncludeModule('crm')){
            global $DB;
            if(!empty($taxtCode)){
                $dbRes = $DB->Query("SELECT * FROM b_uts_crm_company WHERE UF_CRM_1610523219 = '$taxtCode'");
    
                if( $rowCont = $dbRes->Fetch() ){
                    return $rowCont['VALUE_ID'];
                }
            }
        }
    }


    public function getCompanyByIdentificator($ident){

        if(\Bitrix\Main\Loader::IncludeModule('crm')){
            global $DB;
            if(!empty($ident)){
                $dbRes = $DB->Query("SELECT * FROM b_uts_crm_company WHERE UF_CRM_1609155023='$ident'");
    
                if( $rowCont = $dbRes->Fetch() ){
                    return $rowCont['VALUE_ID'];
                }
            }
        }
    }


    public function getDealByIdentificator($ident){
        
        if(\Bitrix\Main\Loader::IncludeModule('crm')){
            global $DB;
            if(!empty($ident)){
                $sql = "SELECT * FROM b_uts_crm_deal WHERE UF_CRM_1610440457='$ident'";
                // echo $sql;
                $dbRes = $DB->Query($sql);
    
                if( $rowCont = $dbRes->Fetch() ){
                    return $rowCont['VALUE_ID'];
                }
            }
        }
    }


    public function FindCompanyByPhone($phone){

        if (!\CModule::IncludeModule('crm')) {
            return false;
        }

        global $DB;    

        $sql = "SELECT * FROM b_crm_field_multi WHERE ENTITY_ID='COMPANY' and TYPE_ID='PHONE' and VALUE='$phone' limit 0,1";

        $res = $DB->Query($sql);

        if($row = $res->fetch()){
            return $row;
        }

    }



    public function FindLeadIDByPhone($phone){        
        global $DB;

        $SQL = "SELECT * FROM b_crm_field_multi WHERE ENTITY_ID='LEAD' AND VALUE='$phone'";

        $r = $DB->Query($SQL);

        if($row = $r->fetch()){
            return $row['ELEMENT_ID'];
        }
    }


    public function FromLeadContact($leadID, $contactID){

        global $DB;

        $sql1 = "SELECT LEAD_ID, CONTACT_ID FROM b_crm_lead_contact WHERE LEAD_ID='$leadID' and CONTACT_ID='$contactID'";
        $r = $DB->Query($sql1);

        if(!$r->fetch()){
            // $sql = "INSERT b_crm_lead_contact set LEAD_ID='$leadID', CONTACT_ID='$contactID', IS_PRIMARY='Y'";
            // $rs = $DB->Query($sql);
            // return $rs;
            return false;
        }
    }

}
