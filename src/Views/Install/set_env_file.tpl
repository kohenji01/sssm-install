{strip}
        <main>
            <h2>{t}.envファイルの設定{/t}</h2>
            <div class="container">
            <table class="table table-bordered">
                <thead>
                <tr><th colspan="2">{t}書込権限テスト{/t}</th></tr>
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

            {if $DATA->checkResult.writable_success}
                <p class="alert-primary">{t}すべてのチェックをクリアしました。{/t}</p>
            {else}
                <p class="alert-danger">{t}すべてのチェックをクリアできていません。{/t}</p>
            {/if}
            </div>

            <div class="container text-left">
                {if $DATA->checkResult.writable_success}
                    <form method="post" action="{$smarty.server.SCRIPT_NAME}/Install/set_env_file/save">
                        <div class="form-group">
                            <label for="sysname">{t}システム名{/t}</label>
                            <input type="text" name="sysname" value="sssm" class="form-control" id="sysname">
                            <small id="baseURLHelp" class="form-text text-muted">{t}インストールするシステム名です。通常変更する必要はありません。{/t}</small>
                        </div>
                        <div class="form-group">
                            <label for="BaseURL">{t}BaseURL{/t}</label>
                            <input type="text" name="baseURL" value="{$DATA->baseUrl}" class="form-control" id="BaseURL">
                            <small id="baseURLHelp" class="form-text text-muted">{t}CI4環境変数のbaseURLです。{/t}</small>
                        </div>
                        <div class="form-group">
                            <label for="indexPage">{t}IndexPage{/t}</label>
                            <input type="text" name="indexPage" value="{$DATA->indexPage}" class="form-control" id="indexPage">
                            <small id="baseURLHelp" class="form-text text-muted">{t}CI4環境変数のindexPageです。{/t}</small>
                        </div>
                        {if $DATA->validEnv}
                            <p class="form-group bg-info">
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
{/strip}
