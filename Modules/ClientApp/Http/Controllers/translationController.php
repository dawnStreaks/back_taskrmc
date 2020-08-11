<?php

namespace Modules\ClientApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\ClientApp\Entities\Translations;
use Modules\ClientApp\User;


class translationController extends Controller
{
    public function index()
    {

        $tables = \DB::select('SHOW TABLES');
        $table1 = array_map('current', $tables);
        foreach ($table1 as $table) {

            $tablenames[] = array('name' => $table);

        }
        if ($tables) {
            return [
                'data' => $tablenames,

            ];
        }


        return response()->json(["code" => 400]);
    }

    Public function gettablecolumns(Request $request)
    {

        $fields = \DB::getSchemaBuilder()->getColumnListing($request->tablename);
        if (($key = array_search('id', $fields)) !== false) {
            unset($fields[$key]);
        }
        if (($key = array_search('created_at', $fields)) !== false) {
            unset($fields[$key]);
        }
        if (($key = array_search('updated_at', $fields)) !== false) {
            unset($fields[$key]);
        }

        return $fields;

    }

    public function store(Request $request)
    {
        $model = new Translations($request->all());

        $model->setTable('trans_table');
        $model->tenant_id = $request->TenantId;
        $model->key_type = $request->TranslationType;
        $model->key_pos = $request->TranslationPos;

        if ($request->TableName != NULL) {
            $model->key_name = $request->TableName;
            $tanslationkey = \DB::table('trans_table')->where('key_name', $model->key_name)
                ->where('key_pos', $model->key_pos)
                ->first();

        } else {
            $model->key_name = $request->Keyname;
            $tanslationkey = \DB::table('trans_table')->where('key_name', $model->key_name)
                ->first();

        }

        $model->value_ar = $request->ArValue;
        $model->value_en = $request->EngValue;

//
//        $model->svalue_ar=empty($request->ArsValue) ? '' : $request->ArsValue;
//        $model->svalue_en=empty($request->EngsValue) ? '' : $request->EngsValue;

//        if(!isset($model->svalue_ar)){
//            $model->svalue_en = 'NA';
//            $model->svalue_ar = 'NA';
//
//        }
//        else{
//            $model->svalue_ar = $request->EngsValue;
//            $model->svalue_en = $request->ArsValue;
//        }
//


        if (empty($request->KeyId) || !isset($request->KeyId) || $request->KeyId === 0) {
            if (!$tanslationkey) {

                if ($model->save($request->all())) {
                    return response()->json([
                        "code" => 200,
                        "msg" => "data inserted successfully"
                    ]);
                } else {
                    return response()->json([
                        "code" => 422,
                        "msg" => "translation key already exists"
                    ]);

                }
            }
        } else {
            $model1 = new Translations($request->all());

            $model1->setTable('trans_table');
            $id = $request->get('KeyId');
            $query = $model1->find($id);
            $updates["tenant_id"] = $request->TenantId;
            $updates["key_type"] = $request->TranslationType;;
            $updates["key_pos"] = $request->TranslationPos;
            $updates["key_name"] = $request->Keyname;
            $updates["value_ar"] = $request->ArValue;
            $updates["value_en"] = $request->EngValue;
            $updates["svalue_ar"] = $request->svalue_ar;
            $updates["svalue_en"] = $request->svalue_en;
            if ($query->update($updates)) {
                return response()->json([
                    "code" => 200,
                    "msg" => "data updated successfully"
                ]);
            }
        }


        return response()->json(["code" => 400, 'msg' => 'Same Data So you can not update translation']);
    }

    public function loadtranslations()
    {
        $translations = \DB::table("trans_table")
            ->select(\DB::raw('*'))
            ->orderBy('id', 'DESC')
            ->get();
        $i = 0;
        foreach ($translations as $translation) {

            /*$translations->key_type = ($translation->key_type == 'l') ? "Label" : (($translation->key_type == 'm') ? "Message" : "Column");*/

            $translations->key_type = (
            ($translation->key_type == "l") ? "Label" :
                (($translation->key_type == "m") ? "Message" :
                    (($translation->key_type == "c") ? "Column" : "Column"))
            );

            $translations[$i]->key_type = $translations->key_type;
            $i++;
        }
        if ($translations) {
            return [
                "code" => 200,
                'data' => $translations,

            ];
        }

        return response()->json(["code" => 400]);
    }

    public function translationdatabyId($value)
    {
        $editdata = \DB::select(\DB::raw("select * from trans_table where id=$value"));


        return response()->json(
            $editdata
        );
    }

    public function gettranslations()
    {
        $keyposarray = '';
        $translations = \DB::table("trans_table")
            ->select(\DB::raw('*'))
            ->get();
        foreach ($translations as $key => $value) {
//            if(key-type=='c'){
//                $keyposarray[]=$value->key_pos;
//            }
            //$keytypearray[$value->key_name]=$value->key_type;
            if (isset($value->key_pos)) {
                $keyname = $value->key_name . '@' . $value->key_pos . '@' . $value->key_type;
            } else {
                $keyname = $value->key_name . '@' . $value->key_type;
            }
            // $keyname=$value->key_name.'@'.$value->key_pos.'@'.$value->key_type;
//           $ararray[$value->key_name] = $value->value_ar;
//           $enarray[$value->key_name] = $value->value_en;
            $ararray[$keyname] = $value->value_ar;
            $enarray[$keyname] = $value->value_en;
        }

        if ($translations) {

            $trans1 = array("en" => $enarray);
            $trans2 = array("ar" => $ararray);
            return [
                "code" => 200,
                'data' => array_merge($trans1, $trans2),

            ];
        }

        return response()->json(["code" => 400]);
    }

    public function destroy($id)
    {
        $model = new Translations();
        $model->setTable('trans_table');
        $query = $model->find($id);
        if ($query->delete()) {
            return response()->json([
                "code" => 200,
                "msg" => "deleted the record"
            ]);
        }
    }


}
