<?php
/**
 * AI Concierge Supporting Classes - 完全実装
 * 
 * @package Grant_Insight_Ultimate
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

/**
 * ChatGPT Client - OpenAI API完全統合
 * Note: 12-ai_concierge_function.phpで既に定義されている場合はスキップ
 */
if (!class_exists('GI_ChatGPT_Client')) {
class GI_ChatGPT_Client {
    
    private $api_key;
    private $model;
    private $max_tokens;
    private $temperature;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $timeout = 60;
    
    public function __construct($settings) {
        $this->api_key = $settings['openai_api_key'] ?? '';
        $this->model = $settings['model'] ?? 'gpt-4';
        $this->max_tokens = $settings['max_tokens'] ?? 1500;
        $this->temperature = $settings['temperature'] ?? 0.7;
    }
    
    /**
     * ChatGPT API呼び出し
     */
    public function generate_response($messages, $stream = false) {
        // APIキーが設定されていない場合はモック応答を返す
        if (empty($this->api_key) || $this->api_key === 'demo') {
            return $this->generate_mock_response($messages);
        }
        
        $request_body = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->max_tokens,
            'temperature' => $this->temperature,
            'stream' => $stream
        ];
        
        $response = wp_remote_post($this->api_url, [
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($request_body)
        ]);
        
        if (is_wp_error($response)) {
            error_log('ChatGPT API Error: ' . $response->get_error_message());
            return $this->generate_fallback_response($messages);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return $this->generate_fallback_response($messages);
        }
        
        return $data;
    }
    
    /**
     * モック応答生成（デモ用）
     */
    private function generate_mock_response($messages) {
        $user_message = end($messages)['content'] ?? '';
        
        // キーワードベースの応答生成
        $responses = [
            'IT導入補助金' => "【IT導入補助金について】\n\nIT導入補助金は、中小企業・小規模事業者のITツール導入を支援する制度です。\n\n✅ **補助率**: 1/2～3/4\n✅ **補助額**: 30万円～450万円\n✅ **対象**: ITツールの導入費用\n\n申請には以下のステップがあります：\n1. IT導入支援事業者の選定\n2. ITツールの選定\n3. 申請書類の準備\n4. 電子申請\n\n詳細な要件や申請方法についてお答えできます。",
            
            'ものづくり補助金' => "【ものづくり補助金について】\n\nものづくり補助金は、革新的サービス開発・生産プロセス改善を支援します。\n\n✅ **補助率**: 1/2～2/3\n✅ **補助上限**: 1,250万円\n✅ **対象**: 設備投資、システム構築費等\n\n採択のポイント：\n• 革新性の明確化\n• 事業計画の具体性\n• 実現可能性の証明\n\n申請準備から採択まで、全面的にサポートします。",
            
            '事業再構築補助金' => "【事業再構築補助金について】\n\n事業再構築補助金は、ポストコロナ時代の経済変化に対応する事業再構築を支援します。\n\n✅ **補助率**: 1/2～3/4\n✅ **補助上限**: 1億円\n✅ **要件**: 売上減少要件、事業再構築要件\n\n支援類型：\n• 成長枠\n• グリーン成長枠\n• 産業構造転換枠\n• サプライチェーン強靭化枠\n\nあなたの事業に最適な枠をご提案します。",
            
            'default' => "ご質問ありがとうございます。\n\n補助金・助成金の活用には、以下のポイントが重要です：\n\n1. **事業計画の明確化**: 具体的な目標と実行計画\n2. **要件の確認**: 対象者要件、事業要件の適合性\n3. **書類の準備**: 必要書類の早期準備\n4. **スケジュール管理**: 締切に余裕を持った申請\n\n具体的にどのような支援をお探しですか？業種や規模、用途などを教えていただければ、最適な補助金をご提案します。"
        ];
        
        // キーワードマッチング
        $response_text = $responses['default'];
        foreach ($responses as $keyword => $response) {
            if ($keyword !== 'default' && mb_strpos($user_message, $keyword) !== false) {
                $response_text = $response;
                break;
            }
        }
        
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $response_text
                    ]
                ]
            ],
            'usage' => [
                'total_tokens' => 500
            ]
        ];
    }
    
    /**
     * フォールバック応答
     */
    private function generate_fallback_response($messages) {
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => "申し訳ございません。現在、システムが混雑しております。\n少し時間をおいて再度お試しください。\n\nお急ぎの場合は、以下の情報が役立つかもしれません：\n• 補助金の一覧は検索機能でご確認いただけます\n• 各補助金の詳細ページで要件や申請方法を確認できます\n• よくある質問もご参照ください"
                    ]
                ]
            ],
            'usage' => [
                'total_tokens' => 100
            ]
        ];
    }
}
} // end if class_exists check

/**
 * Session Manager - セッション管理
 */
if (!class_exists('GI_Session_Manager')) {
class GI_Session_Manager {
    
    private $session_table;
    
    public function __construct() {
        global $wpdb;
        $this->session_table = $wpdb->prefix . 'gi_ai_sessions';
    }
    
    /**
     * セッション作成
     */
    public function create_session() {
        global $wpdb;
        
        $session_id = 'session_' . wp_generate_uuid4();
        
        $wpdb->insert($this->session_table, [
            'session_id' => $session_id,
            'user_id' => get_current_user_id() ?: null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => current_time('mysql'),
            'last_activity' => current_time('mysql'),
            'status' => 'active'
        ]);
        
        return $session_id;
    }
    
    /**
     * セッション取得
     */
    public function get_session($session_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->session_table} WHERE session_id = %s",
            $session_id
        ), ARRAY_A);
    }
    
    /**
     * セッション更新
     */
    public function update_session($session_id, $data = []) {
        global $wpdb;
        
        $data['last_activity'] = current_time('mysql');
        
        return $wpdb->update(
            $this->session_table,
            $data,
            ['session_id' => $session_id]
        );
    }
    
    /**
     * セッション終了
     */
    public function end_session($session_id) {
        return $this->update_session($session_id, ['status' => 'ended']);
    }
}
} // end if class_exists check

/**
 * Semantic Search Engine - セマンティック検索エンジン
 */
if (!class_exists('GI_Semantic_Search_Engine')) {
class GI_Semantic_Search_Engine {
    
    /**
     * セマンティック検索実行
     */
    public function search($query, $filters = [], $page = 1, $per_page = 10) {
        global $wpdb;
        
        // クエリの正規化と拡張
        $normalized_query = $this->normalize_query($query);
        $expanded_terms = $this->expand_query($normalized_query);
        
        // 検索パラメータ構築
        $args = [
            'post_type' => 'grant',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => 'relevance',
            'order' => 'DESC',
            's' => $normalized_query
        ];
        
        // フィルター適用
        if (!empty($filters)) {
            $args = $this->apply_filters($args, $filters);
        }
        
        // カスタムクエリで関連性スコアを計算
        add_filter('posts_clauses', [$this, 'add_relevance_scoring'], 10, 2);
        
        $query_obj = new WP_Query($args);
        
        remove_filter('posts_clauses', [$this, 'add_relevance_scoring'], 10);
        
        // 結果の整形
        $results = [];
        if ($query_obj->have_posts()) {
            while ($query_obj->have_posts()) {
                $query_obj->the_post();
                $results[] = $this->format_result(get_post());
            }
            wp_reset_postdata();
        }
        
        return [
            'results' => $results,
            'total' => $query_obj->found_posts,
            'pages' => $query_obj->max_num_pages,
            'current_page' => $page,
            'query_info' => [
                'original' => $query,
                'normalized' => $normalized_query,
                'expanded' => $expanded_terms
            ]
        ];
    }
    
    /**
     * クエリ正規化
     */
    private function normalize_query($query) {
        // 全角→半角変換
        $query = mb_convert_kana($query, 'as');
        
        // 小文字化
        $query = mb_strtolower($query);
        
        // 余分なスペース除去
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        return $query;
    }
    
    /**
     * クエリ拡張
     */
    private function expand_query($query) {
        $synonyms = [
            'IT' => ['IT導入', 'デジタル', 'システム', 'ソフトウェア'],
            'ものづくり' => ['製造', '生産', '加工', '製造業'],
            '補助金' => ['助成金', '支援金', '給付金'],
            '申請' => ['応募', '申し込み', '手続き']
        ];
        
        $expanded = [$query];
        
        foreach ($synonyms as $term => $alternatives) {
            if (mb_strpos($query, $term) !== false) {
                foreach ($alternatives as $alt) {
                    $expanded[] = str_replace($term, $alt, $query);
                }
            }
        }
        
        return array_unique($expanded);
    }
    
    /**
     * 検索結果フォーマット
     */
    private function format_result($post) {
        return [
            'id' => $post->ID,
            'title' => $post->post_title,
            'excerpt' => get_the_excerpt($post->ID),
            'permalink' => get_permalink($post->ID),
            'amount' => get_post_meta($post->ID, 'max_amount', true),
            'deadline' => get_post_meta($post->ID, 'deadline', true),
            'organization' => get_post_meta($post->ID, 'organization', true),
            'success_rate' => get_post_meta($post->ID, 'grant_success_rate', true),
            'relevance_score' => get_post_meta($post->ID, '_relevance_score', true)
        ];
    }
    
    /**
     * フィルター適用
     */
    private function apply_filters($args, $filters) {
        // 業種フィルター
        if (!empty($filters['industry'])) {
            $args['meta_query'][] = [
                'key' => 'target_industry',
                'value' => $filters['industry'],
                'compare' => 'LIKE'
            ];
        }
        
        // 金額フィルター
        if (!empty($filters['amount_min']) || !empty($filters['amount_max'])) {
            $args['meta_query'][] = [
                'key' => 'max_amount_numeric',
                'value' => [$filters['amount_min'] ?? 0, $filters['amount_max'] ?? 999999999],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ];
        }
        
        // 地域フィルター
        if (!empty($filters['region'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'grant_prefecture',
                'field' => 'slug',
                'terms' => $filters['region']
            ];
        }
        
        return $args;
    }
    
    /**
     * 関連性スコアリング
     */
    public function add_relevance_scoring($clauses, $wp_query) {
        global $wpdb;
        
        $search_term = $wp_query->get('s');
        if (empty($search_term)) {
            return $clauses;
        }
        
        // タイトルと内容の重み付けマッチング
        $clauses['fields'] .= ", 
            (
                CASE WHEN {$wpdb->posts}.post_title LIKE '%{$search_term}%' THEN 10 ELSE 0 END +
                CASE WHEN {$wpdb->posts}.post_content LIKE '%{$search_term}%' THEN 5 ELSE 0 END +
                CASE WHEN {$wpdb->posts}.post_excerpt LIKE '%{$search_term}%' THEN 3 ELSE 0 END
            ) AS relevance_score";
        
        $clauses['orderby'] = 'relevance_score DESC, ' . $clauses['orderby'];
        
        return $clauses;
    }
}
} // end if class_exists check

/**
 * Context Manager - コンテキスト管理
 */
if (!class_exists('GI_Context_Manager')) {
class GI_Context_Manager {
    
    private $context_cache = [];
    
    /**
     * コンテキスト取得
     */
    public function get_context($session_id) {
        if (isset($this->context_cache[$session_id])) {
            return $this->context_cache[$session_id];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'gi_ai_conversations';
        
        $context = $wpdb->get_var($wpdb->prepare(
            "SELECT context FROM {$table} 
             WHERE session_id = %s 
             ORDER BY created_at DESC 
             LIMIT 1",
            $session_id
        ));
        
        $context = $context ? json_decode($context, true) : [];
        $this->context_cache[$session_id] = $context;
        
        return $context;
    }
    
    /**
     * コンテキスト更新
     */
    public function update_context($context, $message, $intent) {
        // ユーザー情報の抽出と更新
        $context = $this->extract_user_info($context, $message);
        
        // 現在のトピック更新
        $context['current_topic'] = $intent['intent'] ?? 'general';
        
        // 会話の流れを記録
        if (!isset($context['conversation_flow'])) {
            $context['conversation_flow'] = [];
        }
        $context['conversation_flow'][] = [
            'intent' => $intent['intent'],
            'timestamp' => time()
        ];
        
        // 重要なエンティティの抽出
        $context['entities'] = $this->extract_entities($message);
        
        return $context;
    }
    
    /**
     * ユーザー情報抽出
     */
    private function extract_user_info($context, $message) {
        // 業種の抽出
        $industries = ['製造業', '小売業', 'IT', 'サービス業', '建設業', '飲食業'];
        foreach ($industries as $industry) {
            if (mb_strpos($message, $industry) !== false) {
                $context['user_industry'] = $industry;
                break;
            }
        }
        
        // 規模の抽出
        if (preg_match('/(\d+)人/', $message, $matches)) {
            $context['company_size'] = intval($matches[1]);
        }
        
        // 地域の抽出
        $prefectures = ['東京', '大阪', '愛知', '福岡', '北海道'];
        foreach ($prefectures as $pref) {
            if (mb_strpos($message, $pref) !== false) {
                $context['user_location'] = $pref;
                break;
            }
        }
        
        return $context;
    }
    
    /**
     * エンティティ抽出
     */
    private function extract_entities($message) {
        $entities = [];
        
        // 金額の抽出
        if (preg_match('/(\d+)万円/', $message, $matches)) {
            $entities['amount'] = intval($matches[1]) * 10000;
        }
        
        // 日付の抽出
        if (preg_match('/(\d{4}年\d{1,2}月\d{1,2}日)/', $message, $matches)) {
            $entities['date'] = $matches[1];
        }
        
        // 補助金名の抽出
        $grant_keywords = ['IT導入補助金', 'ものづくり補助金', '事業再構築補助金', '持続化補助金'];
        foreach ($grant_keywords as $grant) {
            if (mb_strpos($message, $grant) !== false) {
                $entities['grant_name'] = $grant;
                break;
            }
        }
        
        return $entities;
    }
}
} // end if class_exists check

/**
 * Emotion Analyzer - 感情分析
 */
if (!class_exists('GI_Emotion_Analyzer')) {
class GI_Emotion_Analyzer {
    
    /**
     * 感情分析実行
     */
    public function analyze($text) {
        $emotions = [
            'positive' => 0,
            'negative' => 0,
            'neutral' => 0,
            'urgent' => 0,
            'confused' => 0
        ];
        
        // ポジティブキーワード
        $positive_keywords = ['嬉しい', 'ありがとう', '助かる', '良い', '素晴らしい', '期待'];
        foreach ($positive_keywords as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                $emotions['positive'] += 1;
            }
        }
        
        // ネガティブキーワード
        $negative_keywords = ['困った', '難しい', 'できない', '分からない', '問題', '失敗'];
        foreach ($negative_keywords as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                $emotions['negative'] += 1;
            }
        }
        
        // 緊急性キーワード
        $urgent_keywords = ['急ぎ', '至急', 'すぐ', '今日', '明日', '締切'];
        foreach ($urgent_keywords as $keyword) {
            if (mb_strpos($text, $keyword) !== false) {
                $emotions['urgent'] += 1;
            }
        }
        
        // 混乱キーワード
        if (mb_substr_count($text, '？') > 1 || mb_strpos($text, 'どうすれば') !== false) {
            $emotions['confused'] += 1;
        }
        
        // 最も強い感情を特定
        $max_emotion = 'neutral';
        $max_score = 0;
        foreach ($emotions as $emotion => $score) {
            if ($score > $max_score) {
                $max_emotion = $emotion;
                $max_score = $score;
            }
        }
        
        // スコア計算（0-1の範囲）
        $total_score = array_sum($emotions);
        $normalized_score = $total_score > 0 ? $max_score / $total_score : 0.5;
        
        return [
            'emotion' => $max_emotion,
            'score' => $normalized_score,
            'details' => $emotions
        ];
    }
}
} // end if class_exists check

/**
 * Learning System - 学習システム
 */
if (!class_exists('GI_Learning_System')) {
class GI_Learning_System {
    
    private $learning_table;
    
    public function __construct() {
        global $wpdb;
        $this->learning_table = $wpdb->prefix . 'gi_ai_learning';
    }
    
    /**
     * インタラクション記録
     */
    public function record_interaction($query, $response, $intent) {
        global $wpdb;
        
        $query_hash = md5($query);
        
        // 既存レコードの確認
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->learning_table} WHERE query_hash = %s",
            $query_hash
        ));
        
        if ($existing) {
            // 使用回数を増やす
            $wpdb->update(
                $this->learning_table,
                [
                    'usage_count' => $existing->usage_count + 1,
                    'last_used' => current_time('mysql')
                ],
                ['id' => $existing->id]
            );
        } else {
            // 新規レコード作成
            $wpdb->insert($this->learning_table, [
                'query_hash' => $query_hash,
                'original_query' => $query,
                'processed_query' => $this->process_query($query),
                'intent' => $intent['intent'] ?? 'general',
                'results' => wp_json_encode(['response' => $response]),
                'usage_count' => 1,
                'created_at' => current_time('mysql'),
                'last_used' => current_time('mysql')
            ]);
        }
    }
    
    /**
     * フィードバック記録
     */
    public function record_feedback($session_id, $message_id, $rating, $type) {
        global $wpdb;
        
        // フィードバックに基づいて学習データを更新
        $wpdb->query($wpdb->prepare(
            "UPDATE {$this->learning_table} 
             SET feedback_score = %d 
             WHERE id IN (
                SELECT learning_id FROM {$wpdb->prefix}gi_ai_conversations 
                WHERE session_id = %s AND id = %d
             )",
            $rating,
            $session_id,
            $message_id
        ));
    }
    
    /**
     * クエリ処理
     */
    private function process_query($query) {
        // 正規化
        $processed = mb_convert_kana($query, 'as');
        $processed = mb_strtolower($processed);
        
        // ストップワード除去
        $stopwords = ['の', 'を', 'に', 'は', 'が', 'で', 'と', 'から', 'まで'];
        foreach ($stopwords as $word) {
            $processed = str_replace($word, ' ', $processed);
        }
        
        // 余分なスペース除去
        $processed = preg_replace('/\s+/', ' ', trim($processed));
        
        return $processed;
    }
    
    /**
     * 学習データの最適化
     */
    public function optimize_learning_data() {
        global $wpdb;
        
        // 90日以上使用されていないデータを削除
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->learning_table} 
             WHERE last_used < %s AND feedback_score < 3",
            date('Y-m-d H:i:s', strtotime('-90 days'))
        ));
        
        // 低評価のデータをアーカイブ
        $wpdb->query(
            "UPDATE {$this->learning_table} 
             SET archived = 1 
             WHERE feedback_score = 1 AND usage_count < 3"
        );
    }
}
} // end if class_exists check

// クラスのインスタンス化ヘルパー
if (!function_exists('gi_get_ai_concierge_instance')) {
    function gi_get_ai_concierge_instance() {
        return GI_AI_Concierge::getInstance();
    }
}