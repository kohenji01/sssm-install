<main>
    <div class="container">
        <h1>{t}sssmインストーラー{/t}</h1>

        <h2>{t}ステップ１{/t}</h2>

        <h3>{t}環境のチェックを行います{/t}</h3>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{t}パス{/t}</th><th>{t}チェック結果{/t}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$DATA.checkResult.writable item=item key=key}
                <tr>
                    <th class="text-left">{$key}</th>
                    <th class="text-{$item}">{if $item=='danger'}✘{else}✔{/if}</th>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {if $DATA.checkResult.writable_success}
            <p class="alert-primary">{t}すべてのチェックをクリアしました。{/t}</p>
            <a href="{$DATA.site_url}install" class="btn btn-secondary">{t}前へ{/t}</a>
            <a href="{$DATA.site_url}install/dbinfo" class="btn btn-primary">{t}次へ{/t}</a>
        {else}
            <p class="alert-danger">{t}すべてのチェックをクリアできていません。{/t}</p>
            <a href="{$DATA.site_url}install/checkenv" class="btn btn-info">{t}再試行{/t}</a>

            <a href="{$DATA.site_url}install" class="btn btn-secondary">{t}前へ{/t}</a>

        {/if}
    </div>
</main>