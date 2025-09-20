<?php
/**
 * Grant Insight Perfect - 6. Admin Functions File
 *
 * 管理画面のカスタマイズ（スクリプト読込、投稿一覧へのカラム追加、
 * メタボックス追加、カスタムメニュー追加など）を担当します。
 *
 * @package Grant_Insight_Perfect
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}



/**
 * 管理画面カスタマイズ（強化版）
 */
function gi_admin_init() {
    // 管理画面スタイル
    add_action('admin_head', function() {
        echo '<style>
        .gi-admin-notice {
            border-left: 4px solid #10b981;
            background: #ecfdf5;
            padding: 12px 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .gi-admin-notice h3 {
            color: #047857;
            margin: 0 0 8px 0;
            font-size: 16px;
        }
        .gi-admin-notice p {
            color: #065f46;
            margin: 0;
        }
        </style>';
    });
    
    // 投稿一覧カラム追加
    add_filter('manage_grant_posts_columns', 'gi_add_grant_columns');
    add_action('manage_grant_posts_custom_column', 'gi_grant_column_content', 10, 2);
}
add_action('admin_init', 'gi_admin_init');

/**
 * 助成金一覧にカスタムカラムを追加
 */
function gi_add_grant_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['gi_prefecture'] = '都道府県';
            $new_columns['gi_amount'] = '金額';
            $new_columns['gi_organization'] = '実施組織';
            $new_columns['gi_status'] = 'ステータス';
        }
    }
    return $new_columns;
}

/**
 * カスタムカラムに内容を表示
 */
function gi_grant_column_content($column, $post_id) {
    switch ($column) {
        case 'gi_prefecture':
            $prefecture_terms = get_the_terms($post_id, 'grant_prefecture');
            if ($prefecture_terms && !is_wp_error($prefecture_terms)) {
                echo gi_safe_escape($prefecture_terms[0]->name);
            } else {
                echo '－';
            }
            break;
        case 'gi_amount':
            $amount = gi_safe_get_meta($post_id, 'max_amount');
            echo $amount ? gi_safe_escape($amount) . '万円' : '－';
            break;
        case 'gi_organization':
            echo gi_safe_escape(gi_safe_get_meta($post_id, 'organization', '－'));
            break;
        case 'gi_status':
            $status = gi_map_application_status_ui(gi_safe_get_meta($post_id, 'application_status', 'open'));
            $status_labels = array(
                'active' => '<span style="color: #059669;">募集中</span>',
                'upcoming' => '<span style="color: #d97706;">募集予定</span>',
                'closed' => '<span style="color: #dc2626;">募集終了</span>'
            );
            echo $status_labels[$status] ?? $status;
            break;
    }
}

// 重要ニュース機能は削除されました（未使用のため）


/**
 * 管理メニューの追加
 */
function gi_add_admin_menu() {
    // 都道府県データ初期化
    add_management_page(
        '都道府県データ初期化',
        '都道府県データ初期化',
        'manage_options',
        'gi-prefecture-init',
        'gi_add_prefecture_init_button'
    );
    
    // AI設定メニュー追加
    add_menu_page(
        'AI検索設定',
        'AI検索設定',
        'manage_options',
        'gi-ai-settings',
        'gi_ai_settings_page',
        'dashicons-search',
        30
    );
    
    // AI検索統計サブメニュー
    add_submenu_page(
        'gi-ai-settings',
        'AI検索統計',
        '統計・レポート',
        'manage_options',
        'gi-ai-statistics',
        'gi_ai_statistics_page'
    );
}
add_action('admin_menu', 'gi_add_admin_menu');

/**
 * 都道府県データ初期化ページの表示内容
 */
function gi_add_prefecture_init_button() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_POST['init_prefecture_data']) && isset($_POST['prefecture_nonce']) && wp_verify_nonce($_POST['prefecture_nonce'], 'init_prefecture')) {
        // `gi_setup_prefecture_taxonomy_data` は initial-setup.php にある想定
        if (function_exists('gi_setup_prefecture_taxonomy_data')) {
            gi_setup_prefecture_taxonomy_data();
            echo '<div class="notice notice-success"><p>都道府県データを初期化しました。</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>エラー: 初期化関数が見つかりませんでした。</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h2>都道府県データ初期化</h2>
        <form method="post">
            <?php wp_nonce_field('init_prefecture', 'prefecture_nonce'); ?>
            <p>助成金の都道府県データとサンプルデータを初期化します。</p>
            <p class="description">この操作は既存の都道府県タクソノミーに不足しているデータを追加するもので、既存のデータを削除するものではありません。</p>
            <input type="submit" name="init_prefecture_data" class="button button-primary" value="都道府県データを初期化" />
        </form>
    </div>
    <?php
}

/**
 * AI設定ページ
 */
function gi_ai_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // 設定の保存処理
    if (isset($_POST['save_ai_settings']) && wp_verify_nonce($_POST['ai_settings_nonce'], 'gi_ai_settings')) {
        $settings = [
            'enable_ai_search' => isset($_POST['enable_ai_search']) ? 1 : 0,
            'enable_voice_input' => isset($_POST['enable_voice_input']) ? 1 : 0,
            'enable_ai_chat' => isset($_POST['enable_ai_chat']) ? 1 : 0,
            'openai_api_key' => sanitize_text_field($_POST['openai_api_key'] ?? ''),
            'openai_model' => sanitize_text_field($_POST['openai_model'] ?? 'gpt-3.5-turbo'),
            'max_tokens' => intval($_POST['max_tokens'] ?? 1000),
            'temperature' => floatval($_POST['temperature'] ?? 0.7),
            'rate_limit' => intval($_POST['rate_limit'] ?? 60),
            'cache_duration' => intval($_POST['cache_duration'] ?? 3600),
            'enable_analytics' => isset($_POST['enable_analytics']) ? 1 : 0
        ];
        
        update_option('gi_ai_settings', $settings);
        echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
    }
    
    // 現在の設定を取得
    $settings = get_option('gi_ai_settings', [
        'enable_ai_search' => 1,
        'enable_voice_input' => 1,
        'enable_ai_chat' => 1,
        'openai_api_key' => '',
        'openai_model' => 'gpt-3.5-turbo',
        'max_tokens' => 1000,
        'temperature' => 0.7,
        'rate_limit' => 60,
        'cache_duration' => 3600,
        'enable_analytics' => 1
    ]);
    ?>
    <div class="wrap">
        <h1>AI検索設定</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('gi_ai_settings', 'ai_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th colspan="2">
                        <h2>基本設定</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">AI検索を有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_ai_search" value="1" 
                                <?php checked($settings['enable_ai_search'], 1); ?>>
                            AIによる高度な検索機能を有効にする
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">音声入力を有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_voice_input" value="1" 
                                <?php checked($settings['enable_voice_input'], 1); ?>>
                            音声による検索入力を有効にする
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">AIチャットを有効化</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_ai_chat" value="1" 
                                <?php checked($settings['enable_ai_chat'], 1); ?>>
                            AIアシスタントとのチャット機能を有効にする
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2">
                        <h2>OpenAI API設定</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">APIキー</th>
                    <td>
                        <input type="password" name="openai_api_key" class="regular-text" 
                            value="<?php echo esc_attr($settings['openai_api_key']); ?>"
                            placeholder="sk-...">
                        <p class="description">OpenAI APIキーを入力してください。
                            <a href="https://platform.openai.com/api-keys" target="_blank">APIキーを取得</a>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">使用モデル</th>
                    <td>
                        <select name="openai_model">
                            <option value="gpt-3.5-turbo" <?php selected($settings['openai_model'], 'gpt-3.5-turbo'); ?>>
                                GPT-3.5 Turbo (高速・低コスト)
                            </option>
                            <option value="gpt-4" <?php selected($settings['openai_model'], 'gpt-4'); ?>>
                                GPT-4 (高精度・高コスト)
                            </option>
                            <option value="gpt-4-turbo-preview" <?php selected($settings['openai_model'], 'gpt-4-turbo-preview'); ?>>
                                GPT-4 Turbo (最新・バランス型)
                            </option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">最大トークン数</th>
                    <td>
                        <input type="number" name="max_tokens" value="<?php echo esc_attr($settings['max_tokens']); ?>" 
                            min="100" max="4000" step="100">
                        <p class="description">AIの応答の最大長（100-4000）</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Temperature</th>
                    <td>
                        <input type="number" name="temperature" value="<?php echo esc_attr($settings['temperature']); ?>" 
                            min="0" max="2" step="0.1">
                        <p class="description">AIの創造性レベル（0:確定的 - 2:創造的）</p>
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2">
                        <h2>パフォーマンス設定</h2>
                    </th>
                </tr>
                
                <tr>
                    <th scope="row">レート制限</th>
                    <td>
                        <input type="number" name="rate_limit" value="<?php echo esc_attr($settings['rate_limit']); ?>" 
                            min="10" max="1000">
                        <span>リクエスト/時間</span>
                        <p class="description">1時間あたりのユーザー毎のリクエスト上限</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">キャッシュ期間</th>
                    <td>
                        <input type="number" name="cache_duration" value="<?php echo esc_attr($settings['cache_duration']); ?>" 
                            min="60" max="86400">
                        <span>秒</span>
                        <p class="description">AI応答のキャッシュ保持時間（60-86400秒）</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">分析データ収集</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_analytics" value="1" 
                                <?php checked($settings['enable_analytics'], 1); ?>>
                            検索と利用状況の統計データを収集する
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_ai_settings" class="button-primary" value="設定を保存">
            </p>
        </form>
        
        <div class="gi-admin-notice">
            <h3>💡 設定のヒント</h3>
            <p>• OpenAI APIキーは必須です。まずはGPT-3.5 Turboから始めることをお勧めします。</p>
            <p>• Temperatureを低く設定すると、より確実で一貫性のある応答が得られます。</p>
            <p>• レート制限を適切に設定して、API使用量を管理してください。</p>
        </div>
        
        <?php if (!empty($settings['openai_api_key'])): ?>
        <div class="gi-admin-notice" style="border-left-color: #3b82f6; background: #eff6ff;">
            <h3 style="color: #1e40af;">✅ API接続状態</h3>
            <p style="color: #1e3a8a;">APIキーが設定されています。AI機能が利用可能です。</p>
            <p>
                <button type="button" class="button" onclick="testOpenAIConnection()">接続テスト</button>
            </p>
        </div>
        
        <script>
        function testOpenAIConnection() {
            const button = event.target;
            button.disabled = true;
            button.textContent = 'テスト中...';
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=gi_test_openai_connection&nonce=<?php echo wp_create_nonce('gi_test_openai'); ?>',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ 接続成功！OpenAI APIが正常に動作しています。');
                } else {
                    alert('❌ 接続失敗: ' + (data.data || '不明なエラー'));
                }
            })
            .catch(error => {
                alert('❌ ネットワークエラー: ' + error.message);
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = '接続テスト';
            });
        }
        </script>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * AI統計ページ
 */
function gi_ai_statistics_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    
    // 統計データの取得
    $search_table = $wpdb->prefix . 'gi_search_history';
    $chat_table = $wpdb->prefix . 'gi_chat_history';
    $voice_table = $wpdb->prefix . 'gi_voice_history';
    
    // 総検索数
    $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM $search_table");
    
    // 総チャット数
    $total_chats = $wpdb->get_var("SELECT COUNT(*) FROM $chat_table WHERE message_type = 'user'");
    
    // 総音声入力数
    $total_voice = $wpdb->get_var("SELECT COUNT(*) FROM $voice_table");
    
    // 人気の検索クエリ
    $popular_queries = $wpdb->get_results("
        SELECT search_query, COUNT(*) as count 
        FROM $search_table 
        GROUP BY search_query 
        ORDER BY count DESC 
        LIMIT 10
    ");
    
    // 時間帯別利用状況
    $hourly_usage = $wpdb->get_results("
        SELECT HOUR(created_at) as hour, COUNT(*) as count 
        FROM $search_table 
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY HOUR(created_at) 
        ORDER BY hour
    ");
    
    ?>
    <div class="wrap">
        <h1>AI検索統計</h1>
        
        <div class="gi-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
            
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333;">総検索数</h3>
                <p style="font-size: 32px; font-weight: bold; color: #10b981; margin: 10px 0;">
                    <?php echo number_format($total_searches); ?>
                </p>
                <p style="color: #666; font-size: 14px;">全期間の検索実行数</p>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333;">チャット利用数</h3>
                <p style="font-size: 32px; font-weight: bold; color: #3b82f6; margin: 10px 0;">
                    <?php echo number_format($total_chats); ?>
                </p>
                <p style="color: #666; font-size: 14px;">AIアシスタントへの質問数</p>
            </div>
            
            <div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #333;">音声入力数</h3>
                <p style="font-size: 32px; font-weight: bold; color: #f59e0b; margin: 10px 0;">
                    <?php echo number_format($total_voice); ?>
                </p>
                <p style="color: #666; font-size: 14px;">音声による検索入力数</p>
            </div>
            
        </div>
        
        <div style="margin-top: 40px; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2>人気の検索キーワード TOP10</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>順位</th>
                        <th>検索キーワード</th>
                        <th>検索回数</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1;
                    foreach ($popular_queries as $query): 
                    ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo esc_html($query->search_query); ?></td>
                        <td><?php echo number_format($query->count); ?>回</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($hourly_usage)): ?>
        <div style="margin-top: 40px; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2>時間帯別利用状況（過去7日間）</h2>
            <div style="display: flex; align-items: flex-end; height: 200px; gap: 10px;">
                <?php 
                $max_count = max(array_column($hourly_usage, 'count'));
                foreach ($hourly_usage as $hour_data): 
                    $height = ($hour_data->count / $max_count) * 100;
                ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                    <div style="background: #3b82f6; width: 100%; height: <?php echo $height; ?>%;" 
                         title="<?php echo $hour_data->hour; ?>時: <?php echo $hour_data->count; ?>件"></div>
                    <span style="font-size: 12px; margin-top: 5px;"><?php echo $hour_data->hour; ?>時</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 40px;">
            <form method="post" action="">
                <?php wp_nonce_field('gi_export_stats', 'export_nonce'); ?>
                <button type="submit" name="export_stats" class="button">統計データをエクスポート (CSV)</button>
                <button type="submit" name="clear_old_data" class="button" 
                        onclick="return confirm('30日以上前のデータを削除します。よろしいですか？')">
                    古いデータをクリア
                </button>
            </form>
        </div>
    </div>
    <?php
    
    // エクスポート処理
    if (isset($_POST['export_stats']) && wp_verify_nonce($_POST['export_nonce'], 'gi_export_stats')) {
        gi_export_ai_statistics();
    }
    
    // 古いデータのクリア
    if (isset($_POST['clear_old_data']) && wp_verify_nonce($_POST['export_nonce'], 'gi_export_stats')) {
        gi_clear_old_ai_data();
        echo '<div class="notice notice-success"><p>古いデータを削除しました。</p></div>';
    }
}

/**
 * 統計データのエクスポート
 */
function gi_export_ai_statistics() {
    global $wpdb;
    
    $search_table = $wpdb->prefix . 'gi_search_history';
    
    $data = $wpdb->get_results("
        SELECT * FROM $search_table 
        ORDER BY created_at DESC 
        LIMIT 1000
    ", ARRAY_A);
    
    if (empty($data)) {
        return;
    }
    
    // CSVヘッダー
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ai-search-statistics-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // ヘッダー行
    fputcsv($output, array_keys($data[0]));
    
    // データ行
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * 古いデータのクリア
 */
function gi_clear_old_ai_data() {
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'gi_search_history',
        $wpdb->prefix . 'gi_chat_history',
        $wpdb->prefix . 'gi_voice_history'
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("
            DELETE FROM $table 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }
}

/**
 * OpenAI接続テスト
 */
function gi_test_openai_connection() {
    check_ajax_referer('gi_test_openai', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('権限がありません');
    }
    
    $settings = get_option('gi_ai_settings', []);
    $api_key = $settings['openai_api_key'] ?? '';
    
    if (empty($api_key)) {
        wp_send_json_error('APIキーが設定されていません');
    }
    
    // OpenAI APIへのテストリクエスト
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'model' => $settings['openai_model'] ?? 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello, this is a test.']
            ],
            'max_tokens' => 10
        ]),
        'timeout' => 10
    ]);
    
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['choices'])) {
        wp_send_json_success('接続成功');
    } else {
        $error = $data['error']['message'] ?? '不明なエラー';
        wp_send_json_error($error);
    }
}
add_action('wp_ajax_gi_test_openai_connection', 'gi_test_openai_connection');