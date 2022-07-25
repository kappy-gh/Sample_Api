<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRanking;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserRankingController extends Controller
{
    // ランキング登録
    public function add_user_ranking(Request $request)
    {
      // リクエストパラメータを受け取る
      $uuid      = (int)$request->input('uuid');
      $user_name = (string)$request->input('user_name');
      $score     = (int)$request->input('score');

      // エラー
      $error_msg = '';

      if($user_name == null)
      {
        $error_msg = '名前が未入力です。';
        return $error_msg;
      }

      $name_max = 10;
      $name_length = strlen($user_name);
      if(!ctype_alnum($user_name) || $name_length > $name_max)
      {
        $error_msg = '名前は10文字以内の英数字のみで入力してください。';
        return $error_msg;
      }

      if($uuid == null) // 新規ユーザー
      {
        // uuidを生成する
        $new_uuid = self::_create_uuid();

        // ユーザー登録
        UserProfile::create([
          'uuid'      => $new_uuid,
          'user_name' => $user_name
        ]);

        // ランキング登録
        $array_user_profile_id = UserProfile::where('uuid', '=', $new_uuid)
              ->first(['id']);
        $user_profile_id = $array_user_profile_id['id'];
        UserRanking::create([
          'user_profile_id' => $user_profile_id,
          'score'           => $score
        ]);
      }
      else // 既存ユーザー
      {
        // uuidに紐づくユーザーのスコアを取得する
        $array_user_high_score = DB::table('user_rankings')
              ->join('user_profiles', 'user_rankings.user_profile_id', '=', 'user_profiles.id')
              ->select('user_rankings.user_profile_id', 'user_rankings.score')
              ->where('user_profiles.uuid', '=', $uuid)
              ->first();
        $user_profile_id = $array_user_high_score->user_profile_id;
        $high_score      = $array_user_high_score->score;

        // ハイスコアならランキングテーブルを更新する
        if($score > $high_score)
        {
          UserRanking::where('user_profile_id', '=', $user_profile_id)
                ->update([
                  'score' => $score
                ]);
        }
      }
    }

    // ランキング情報取得
    public function get_user_ranking()
    {
      // 順位付けの優先度は①スコアが高い②登録日が早い
      $user_scores = DB::table('user_profiles')
            ->join('user_rankings', 'user_profiles.id', '=', 'user_rankings.user_profile_id')
            ->select('user_profiles.user_name', 'user_rankings.score')
            ->orderBy('user_rankings.score', 'desc')
            ->orderBy('user_rankings.updated_at', 'asc')
            ->get();

      return $user_scores;
    }

    // uuidを生成する
    private function _create_uuid()
    {
      // uuidの被り判定
      $same_flg = true;

      // 被りのないものができるまでuuid生成
      while($same_flg == true)
      {
        $new_uuid  = (string)Str::uuid();
        $same_uuid = UserProfile::where('uuid', '=', $new_uuid)
              ->first();
        if(empty($same_uuid))
        {
          $same_flg = false;
        }
      }
      return $new_uuid;
    }
}
