{strip}
<style>
<!--
#envForm .gray{
    background: #ffffff;
}

#envForm div:nth-child(odd){
    background: #ffffff;
}
-->
</style>
<main>
    <h1>{t}sssmインストーラー{/t}</h1>
    <h2>{t}ステップ１{/t}</h2>
    <h3>{t}.envファイルの設定{/t}</h3>
    <div class="container">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2" class="alert-{if $DATA->checkResult.writable_success}success{else}danger{/if}">
                <div>{t}書込権限テスト{/t}<br>
                    {if $DATA->checkResult.writable_success}
                        {t}すべてのチェックをクリアしました。{/t}
                    {else}
                        {t}すべてのチェックをクリアできていません。{/t}
                        <div class="text-center">
                            <a href="{$smarty.server.SCRIPT_NAME}/Install/set_env_file" class="btn btn-primary">{t}再読み込み{/t}</a>
                        </div>
                    {/if}
                </div>
            </th>
        </tr>
        <tr>
            <th>{t}パス{/t}</th><th>{t}チェック結果{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$DATA->checkResult.writable item=item key=key}
            <tr>
                <th class="text-left">{$key}</th>
                <th class="text-{$item}">{if $item=='danger'}✘{else}✔{/if}</th>
            </tr>
        {/foreach}
        </tbody>
    </table>

    </div>

    <div class="container">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th colspan="2" class="alert-{if $DATA->checkResult.checkEnvVarSet_success}success{else}danger{/if}">
                <div>
                    {t}.envファイル設定値チェック{/t}<br>
                    {if $DATA->checkResult.checkEnvVarSet_success}
                        {t}すべてのチェックをクリアしました。追加の設定がある場合は下のフォームを修正してください。{/t}
                    {else}
                        {t}すべてのチェックをクリアできていません。下のフォームを設定してください。{/t}
                        <div class="text-center">
                            <a href="{$smarty.server.SCRIPT_NAME}/Install/set_env_file" class="btn btn-primary">{t}再読み込み（外部で.envを編集している場合）{/t}</a>
                        </div>
                    {/if}
                </div>
            </th>
        </tr>
        <tr>
            <th>{t}パラメータ{/t}</th><th>{t}設定値{/t}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$DATA->checkResult.checkEnvVarSet item=item key=key}
            <tr>
                <th class="text-left">{$key}</th>
                <th class="text-{$item}">
                    {if $item=='danger'}✘{else}{$DATA->checkResult.checkEnvVarList[$key]}{/if}
                    {if isset( $DATA->checkResult.checkEnvVars.message[$key])}
                        <p class="small text-danger">{$DATA->checkResult.checkEnvVars.message[$key]}</p>
                    {/if}
                </th>
            </tr>
        {/foreach}
        </tbody>
    </table>


    </div>

    <div class="container text-left" id="envForm">
        <h3 class="text-center ">{t}.envファイルのパラメータ設定{/t}</h3>
        {if $DATA->checkResult.writable_success}
            <form method="post" action="{$smarty.server.SCRIPT_NAME}/Install/set_env_file/save">
                <div class="form-group gray">
                    <label for="sysname">{t}システム名{/t}</label>
                    <input type="text" name="sysname" value="sssm" class="form-control" id="sysname" readonly="readonly">
                    <small id="sysnameHelp" class="form-text text-muted">{t}インストールするシステム名です。通常変更する必要はありません。{/t}</small>
                </div>
                <div class="form-group gray">
                    <label for="BaseURL">{t}app.BaseURL : このシステムのトップページのURL{/t}</label>
                    <input type="text" name="baseURL" value="{$DATA->baseUrl}" class="form-control" id="BaseURL">
                    <small id="baseURLHelp" class="form-text text-muted">{t}CI4環境変数のbaseURLです。{/t}</small>
                </div>

                <div class="form-group gray">
                    <label for="indexPage">{t}app.IndexPage : CI4の起動ファイル名{/t}</label>
                    <input type="text" name="indexPage" value="{$DATA->indexPage}" class="form-control" id="indexPage">
                    <small id="IndexPageHelp" class="form-text text-muted">{t}CI4環境変数のindexPageです。mod_rewriteが有効の場合、空欄にすることでCI4の起動phpファイル名を隠すことが出来ます。{/t}</small>
                </div>

                <div class="form-group gray">
                    <label for="defaultLocale">{t}app.defaultLocale : デフォルトで表示する言語{/t}</label>
                    {html_options name='defaultLocale' options=$DATA->localeList selected="ja" class="form-control" id="defaultLocale"}
                    <small id="defaultLocaleHelp" class="form-text text-muted">
                        {t}CI4環境変数のdefaultLocaleです。{/t}<br>
                        {t}現在、システム上でCI4の言語ファイルが存在するもののみ表示しています。{/t}<br>
                        {t}これ以外の言語に対応させる場合は後で.envを書き換えることで変更可能です。{/t}
                    </small>
                </div>

                <div class="form-group gray">
                    <label for="negotiateLocale">{t}app.negotiateLocale : ロケール検出{/t}</label>
                    {html_options name='negotiateLocale' options=$DATA->envBool selected="true" class="form-control" id="negotiateLocale"}
                    <small id="negotiateLocaleHelp" class="form-text text-muted">{t}CI4環境変数のnegotiateLocaleです。{/t}</small>
                </div>

                <div class="form-group gray">
                    <label>{t}supportedLocales : システムでサポートする言語{/t}</label><br>
                    <small id="supportedLocalesHelp" class="form-text text-muted">
                        {t}CI4環境変数のsupportedLocalesです。{/t}<br>
                        {t}現在、システム上でCI4の言語ファイルが存在するもののみ表示しています。{/t}<br>
                        {t}これ以外の言語に対応させる場合は後で.envを書き換えることで変更可能です。{/t}<br>
                        {t}対応する順番を数値で入力します。使用しない言語は空にしてください。{/t}
                    </small>
                    {foreach from=$DATA->localeList item=item key=key}
                        <label class="form-inline"><input type="text" name="supportedLocales[{$key}]" value="" class="form-control" style="width: 4rem;">{$item}</label><br>
                    {/foreach}
                </div>

                <div class="form-group gray">
                    <label for="appTimezone">{t}appTimezone : システムのタイムゾーン{/t}</label>
                    <input type="text" name="appTimezone" value="Asia/Tokyo" class="form-control" id="appTimezone">
                    <small id="appTimezoneHelp" class="form-text text-muted">{t}CI4環境変数のappTimezoneです。{/t}</small>
                </div>

                {if $DATA->checkResult.checkEnvVarSet_success}
                    <p class="form-group gray">
                        {t}必要な環境変数がすべて設定された.envファイルが存在します。{/t}
                    </p>
                    <div class="form-group text-center">
                        <button type="submit" class="mt-3 btn btn-warning">{t}.envファイルをこの情報で上書きしてインストールを始めます。元の内容は消去されます。{/t}</button>
                    </div>
                    <div class="form-group text-center">
                        <a href="{$smarty.server.SCRIPT_NAME}/Install/check_writable" class="mt-3 btn btn-primary">{t}.envファイルの上書きをスキップしてインストールを始める{/t}</a>
                    </div>
                {else}
                    <div class="form-group form-check bg-warning">
                        {t}.envファイルが存在していますが、sssm起動に必要な情報がありませんので上書きされます。元の内容は消去されます。{/t}
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="mt-3 btn btn-primary">{t}.envファイルを生成してインストールを始める{/t}</button>
                    </div>
                {/if}
            </form>
        {else}
            <p class="text-danger">{t}{$smarty.const.ROOTPATH}.envファイルが存在しないか書き込む権限がありません。適切な権限を与えてください{/t}</p>
            <div class="text-center">
                <a href="{$smarty.server.SCRIPT_NAME}/Install/set_env_file" class="btn btn-primary">{t}再読み込み{/t}</a>
            </div>
        {/if}
    </div>
</main>
<script>{literal}
  $( function() {
    var availableTags = [{/literal}{$DATA->timeZoneList}{literal}];
    $( "#appTimezone" ).autocomplete({
      source: availableTags
    });
  } );{/literal}
</script>
{/strip}
