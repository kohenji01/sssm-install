{strip}
    {* /** @lang HTML */ *}
<main>
    <h1>{t}sssmインストーラー{/t}</h1>
    <h2>{t}ステップ３{/t}</h2>
    <h3>{t}DBの設定{/t}</h3>

    <div class="container text-left" id="envForm">
        <form method="post" action="{$smarty.server.SCRIPT_NAME}/Install/set_db_info/save" id="db_info_form">
            <div class="form-group gray">
                <label for="db_DBDriver">{t}database.default.DBDriver : DBドライバ{/t}</label>
                <input type="text" name="db_DBDriver" value="{$DATA->db_DBDriver}" class="form-control" id="db_DBDriver" readonly="readonly">
                <small id="db_DBDriverHelp" class="form-text text-muted">{t}DBドライバの種類です。現在はMySQLiのみサポートしています。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_hostname">{t}database.default.hostname : ホスト名{/t}</label>
                <input type="text" name="db_hostname" value="{$DATA->db_hostname}" class="form-control" id="db_hostname">
                <small id="db_hostnameHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.hostnameです。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_port">{t}database.default.port : ポート番号{/t}</label>
                <input type="text" name="db_port" value="{$DATA->db_port}" class="form-control" id="db_port">
                <small id="db_portHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.portです。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_username">{t}database.default.username : ユーザ名{/t}</label>
                <input type="text" name="db_username" value="{$DATA->db_username}" class="form-control" id="db_username">
                <small id="db_usernameHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.usernameです。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_password">{t}database.default.password : パスワード{/t}</label>
                <input type="password" name="db_password" value="" class="form-control" id="db_password">
                <small id="db_passwordHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.passwordです。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_database">{t}database.default.database : DB名{/t}</label>
                <input type="text" name="db_database" value="{$DATA->db_database}" class="form-control" id="db_database">
                <small id="db_databaseHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.databaseです。{/t}</small>
            </div>

            <div class="form-group align-middle text-center">
                <div class="d-inline">
                    <button type="button" class="m-3 btn btn-outline-secondary btnTest" name="func" value="userTest" id="userTest">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;" id="userTestSpin"></span>
                        <span style="display: none;" id="userTestMes"></span>
                        &nbsp;{t}ユーザ確認テスト{/t}
                    </button>
                </div>
                <div class="d-inline">
                    <button type="button" class="m-3 btn btn-outline-secondary btnTest" name="func" value="connectionTest" id="connectionTest">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;" id="connectionTestSpin"></span>
                        <span style="display: none;" id="connectionTestMes"></span>
                        &nbsp;{t}DB接続テスト(DBが存在しない場合は作成を試みます){/t}
                    </button>
                </div>
            </div>


            <div class="form-group gray">
                <label for="db_DBPrefix">{t}database.default.DBPrefix : テーブル名のプリフィックス{/t}</label>
                <input type="text" name="db_DBPrefix" value="{$DATA->db_DBPrefix}" class="form-control" id="db_DBPrefix">
                <small id="db_DBPrefixHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.DBPrefixです。テーブル名の利用に制限のあるときにご利用ください。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_charset">{t}database.default.charset : データベースと通信する際に使用する文字コード{/t}</label>
                <input type="text" name="db_charset" value="{$DATA->db_charset}" class="form-control" id="db_charset">
                <small id="db_charsetHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.charsetです。{/t}</small>
            </div>

            <div class="form-group gray">
                <label for="db_charset">{t}database.default.DBCollat : データベースの照合順序{/t}</label>
                <input type="text" name="db_DBCollat" value="{$DATA->db_DBCollat}" class="form-control" id="db_DBCollat">
                <small id="db_DBCollatHelp" class="form-text text-muted">{t}CI4環境変数のdatabase.default.DBCollatです。{/t}</small>
            </div>

            <div class="form-group text-center">
                <button type="submit" class="mt-3 btn btn-warning">{t}.envファイルにDB情報が追記されます{/t}</button>
            </div>
            <div class="form-group text-center">
                <a href="{$smarty.server.SCRIPT_NAME}/Install/check_writable" class="mt-3 btn btn-primary">{t}DB情報の.envへの書込をスキップして次に進む（.envに正しいDB方法がない場合エラーが出ます）{/t}</a>
            </div>
        </form>
    </div>

</main>
<script>
{literal}
$(function(){
  $("input"). keydown(function(e) {
    if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
      return false;
    } else {
      return true;
    }
  });
});

$('.btnTest').click(function(event) {
  // HTMLでの送信をキャンセル
  event.preventDefault();

  // 操作対象のフォーム要素を取得
  var $form = $("#db_info_form");

  // 送信ボタンを取得
  var $button = $form.find('button');

  // 押されたボタンのID取得
  var id = $(this).attr('id');

  // 送信
  $.ajax({
    url: "{/literal}{$smarty.server.SCRIPT_NAME}/Api/Sssm.Install.Models.DBInit/{literal}" + id,
    type: $form.attr('method'),
    data: $form.serialize(),
    timeout: 10000,  // 単位はミリ秒

    // 送信前
    beforeSend: function(xhr, settings) {
      // ボタンを無効化し、二重送信を防止
      $button.attr('disabled', true);
      $("#"+id+"Spin").show();
    },
    // 応答後
    complete: function(xhr, textStatus) {
      // ボタンを有効化し、再送信を許可
      $button.attr('disabled', false);
    },

    // 通信成功時の処理
    success: function(result, textStatus, xhr) {
      // 入力値を初期化
      // $form[0].reset();
      //
      if( result == 'true' ){
        $("#"+id+"Spin").hide();
        $("#"+id+"Mes").removeClass("text-danger").addClass("text-success").html("{/literal}{t}✔{/t}{literal}").show();
      }else{
        $("#"+id+"Spin").hide();
        $("#"+id+"Mes").removeClass("text-success").addClass("text-danger").html("{/literal}{t}✘{/t}{literal}").show();
      }
      // alert('OK' + result);
    },

    // 通信失敗時の処理
    error: function(xhr, textStatus, error) {
      alert('Communication error.');
    }
  });
});

{/literal}
</script>
{/strip}