<?php

class Bx24Crm{

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