<?php
/**
 * AI-Powered Grant Search Section - Complete Integration
 * 
 * @package Grant_Insight_Perfect
 * @version 1.0.0
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// セッションID生成
$session_id = 'gi_session_' . wp_generate_uuid4();
$nonce = wp_create_nonce('gi_ai_search_nonce');
?>

<!-- AI Grant Search Section -->
<section id="ai-search-section" class="ai-search-wrapper" data-session-id="<?php echo esc_attr($session_id); ?>">
    <div class="ai-container">
        
        <!-- Section Header -->
        <div class="ai-header">
            <span class="ai-badge">AI POWERED</span>
            <h2 class="ai-title">
                <span class="title-en">GRANT SEARCH</span>
                <span class="title-jp">補助金AI検索</span>
            </h2>
            <p class="ai-subtitle">最適な補助金を瞬時に発見</p>
        </div>

        <!-- Main Search Interface -->
        <div class="ai-search-interface">
            
            <!-- Search Bar -->
            <div class="ai-search-bar">
                <div class="search-input-wrapper">
                    <input 
                        type="text" 
                        id="ai-search-input" 
                        class="search-input"
                        placeholder="業種、地域、目的などを入力..."
                        autocomplete="off">
                    <div class="search-actions">
                        <button class="voice-btn" aria-label="音声入力">
                            <svg width="16" height="16" viewBox="0 0 16 16">
                                <path d="M8 11c1.66 0 3-1.34 3-3V3c0-1.66-1.34-3-3-3S5 1.34 5 3v5c0 1.66 1.34 3 3 3z"/>
                                <path d="M13 8c0 2.76-2.24 5-5 5s-5-2.24-5-5H1c0 3.53 2.61 6.43 6 6.92V16h2v-1.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                            </svg>
                        </button>
                        <button id="ai-search-btn" class="search-btn">
                            <span class="btn-text">検索</span>
                            <svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20">
                                <path d="M9 2a7 7 0 100 14A7 7 0 009 2zm0 12a5 5 0 110-10 5 5 0 010 10z"/>
                                <path d="M13.5 13.5L18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="search-suggestions" id="search-suggestions"></div>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters">
                <button class="filter-chip active" data-filter="all">すべて</button>
                <button class="filter-chip" data-filter="it">IT導入</button>
                <button class="filter-chip" data-filter="manufacturing">ものづくり</button>
                <button class="filter-chip" data-filter="startup">創業支援</button>
                <button class="filter-chip" data-filter="sustainability">持続化</button>
                <button class="filter-chip" data-filter="innovation">事業再構築</button>
                <button class="filter-chip" data-filter="employment">雇用関連</button>
            </div>

            <!-- AI Chat & Results -->
            <div class="ai-main-content">
                
                <!-- Left: AI Assistant -->
                <div class="ai-assistant-panel" data-concierge="ultimate">
                    <div class="assistant-header">
                        <div class="assistant-avatar">
                            <div class="ai-avatar-animated">
                                <div class="ai-avatar-ring"></div>
                                <span class="avatar-text">AI</span>
                            </div>
                        </div>
                        <div class="emotion-indicator" title="AI感情状態"></div>
                        <div class="assistant-info">
                            <h3 class="assistant-name">補助金AIコンシェルジュ Ultimate</h3>
                            <span class="assistant-status" data-status="online">オンライン・24/7対応中</span>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chat-messages">
                        <div class="message message-ai fade-in show">
                            <div class="message-avatar">
                                <div class="ai-avatar-animated">
                                    <div class="ai-avatar-ring"></div>
                                    <span class="avatar-text">AI</span>
                                </div>
                            </div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    こんにちは！私は最新AI技術を搭載した補助金コンシェルジュです。<br>
                                    <strong>以下のようなご相談に対応できます：</strong><br>
                                    • 🔍 あなたに最適な補助金の検索<br>
                                    • 📝 申請手続きの詳細な説明<br>
                                    • 📊 採択率向上のアドバイス<br>
                                    • ⏰ 締切日程の管理<br>
                                    <br>
                                    どのようなご要望でもお気軽にお聞かせください！
                                </div>
                                <div class="message-timestamp"><?php echo date('H:i'); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Display -->
                    <div class="chat-stats" id="chat-stats">
                        <div class="stat-item">
                            <span class="stat-label">応答時間</span>
                            <span class="stat-value" id="response-time">0.3秒</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">満足度</span>
                            <span class="stat-value" id="satisfaction">98%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">解決率</span>
                            <span class="stat-value" id="resolution">95%</span>
                        </div>
                    </div>
                    
                    <div class="chat-input-area">
                        <div class="typing-indicator" id="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                        <textarea 
                            id="chat-input" 
                            class="chat-input"
                            placeholder="質問を入力..."
                            rows="1"></textarea>
                        <button id="chat-send" class="chat-send-btn">
                            <svg width="18" height="18" viewBox="0 0 18 18">
                                <path d="M2 9l14-7-5 7 5 7z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Quick Questions -->
                    <div class="quick-questions">
                        <button class="quick-q" data-q="IT導入補助金について詳しく教えて"><span>💻 IT導入補助金</span></button>
                        <button class="quick-q" data-q="ものづくり補助金の申請方法"><span>🏭 ものづくり補助金</span></button>
                        <button class="quick-q" data-q="事業再構築補助金の要件は？"><span>🔄 事業再構築</span></button>
                        <button class="quick-q" data-q="小規模事業者持続化補助金"><span>🏪 持続化補助金</span></button>
                        <button class="quick-q" data-q="私に合う補助金を探して"><span>🎯 オススメ検索</span></button>
                        <button class="quick-q" data-q="申請スケジュールを作成して"><span>📅 スケジュール</span></button>
                    </div>
                </div>

                <!-- Right: Search Results -->
                <div class="search-results-panel">
                    <div class="results-header">
                        <h3 class="results-title">
                            <span id="results-count">0</span>件の補助金
                        </h3>
                        <div class="view-controls">
                            <button class="view-btn active" data-view="grid">
                                <svg width="16" height="16" viewBox="0 0 16 16">
                                    <rect x="1" y="1" width="6" height="6"/>
                                    <rect x="9" y="1" width="6" height="6"/>
                                    <rect x="1" y="9" width="6" height="6"/>
                                    <rect x="9" y="9" width="6" height="6"/>
                                </svg>
                            </button>
                            <button class="view-btn" data-view="list">
                                <svg width="16" height="16" viewBox="0 0 16 16">
                                    <rect x="1" y="2" width="14" height="2"/>
                                    <rect x="1" y="7" width="14" height="2"/>
                                    <rect x="1" y="12" width="14" height="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="results-container" id="results-container">
                        <!-- Initial Featured Grants -->
                        <div class="featured-grants">
                            <?php
                            // 注目の補助金を表示
                            $featured_grants = get_posts([
                                'post_type' => 'grant',
                                'posts_per_page' => 6,
                                'meta_key' => 'is_featured',
                                'meta_value' => '1',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ]);
                            
                            foreach ($featured_grants as $grant):
                                $amount = get_post_meta($grant->ID, 'max_amount', true);
                                $deadline = get_post_meta($grant->ID, 'deadline', true);
                                $organization = get_post_meta($grant->ID, 'organization', true);
                                $success_rate = get_post_meta($grant->ID, 'grant_success_rate', true);
                            ?>
                            <div class="grant-card" data-id="<?php echo $grant->ID; ?>">
                                <div class="card-badge">注目</div>
                                <h4 class="card-title"><?php echo esc_html($grant->post_title); ?></h4>
                                <div class="card-meta">
                                    <span class="meta-item">
                                        <span class="meta-label">最大</span>
                                        <span class="meta-value"><?php echo esc_html($amount ?: '未定'); ?></span>
                                    </span>
                                    <span class="meta-item">
                                        <span class="meta-label">締切</span>
                                        <span class="meta-value"><?php echo esc_html($deadline ?: '随時'); ?></span>
                                    </span>
                                </div>
                                <p class="card-org"><?php echo esc_html($organization); ?></p>
                                <?php if ($success_rate): ?>
                                <div class="card-rate">
                                    <div class="rate-bar">
                                        <div class="rate-fill" style="width: <?php echo $success_rate; ?>%"></div>
                                    </div>
                                    <span class="rate-text">採択率 <?php echo $success_rate; ?>%</span>
                                </div>
                                <?php endif; ?>
                                <a href="<?php echo get_permalink($grant->ID); ?>" class="card-link">
                                    詳細を見る
                                    <svg width="12" height="12" viewBox="0 0 12 12">
                                        <path d="M2 6h8m0 0L7 3m3 3L7 9"/>
                                    </svg>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="results-loading" id="results-loading">
                        <div class="loading-spinner"></div>
                        <span>検索中...</span>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number" data-count="<?php 
                        $grant_posts = wp_count_posts('grant');
                        echo isset($grant_posts->publish) ? $grant_posts->publish : 0;
                    ?>">0</span>
                    <span class="stat-label">登録補助金</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-count="47">0</span>
                    <span class="stat-label">対応都道府県</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">AI対応</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">0.3秒</span>
                    <span class="stat-label">平均応答時間</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI Concierge Ultimate Scripts & Styles -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/ai-concierge-ultimate.css?v=2.0.0">
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/ai-concierge-ultimate.js?v=2.0.0" defer></script>

<style>
/* AI Search Section Styles */
.ai-search-wrapper {
    position: relative;
    padding: 80px 0;
    background: #fff;
    font-family: -apple-system, "SF Pro Display", "Helvetica Neue", "Hiragino Sans", sans-serif;
    overflow: hidden;
}

.ai-search-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #000, transparent);
}

.ai-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header */
.ai-header {
    text-align: center;
    margin-bottom: 60px;
}

.ai-badge {
    display: inline-block;
    padding: 6px 16px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.15em;
    border-radius: 20px;
    margin-bottom: 20px;
}

.ai-title {
    margin: 0;
}

.title-en {
    display: block;
    font-size: 48px;
    font-weight: 900;
    letter-spacing: -0.02em;
    line-height: 1;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #000 0%, #333 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.title-jp {
    display: block;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.1em;
    color: #666;
}

.ai-subtitle {
    margin: 16px 0 0;
    font-size: 16px;
    color: #333;
}

/* Search Bar */
.ai-search-bar {
    position: relative;
    max-width: 720px;
    margin: 0 auto 32px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8f8f8;
    border: 2px solid transparent;
    border-radius: 60px;
    transition: all 0.3s;
}

.search-input-wrapper:focus-within {
    background: #fff;
    border-color: #000;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.search-input {
    flex: 1;
    padding: 18px 24px;
    background: none;
    border: none;
    font-size: 16px;
    outline: none;
}

.search-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-right: 8px;
}

.voice-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: none;
    color: #999;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.voice-btn:hover {
    background: #f0f0f0;
    color: #000;
}

.search-btn {
    height: 44px;
    padding: 0 24px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 22px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.search-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.search-btn:active {
    transform: scale(0.98);
}

.btn-icon {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

/* Search Suggestions */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    display: none;
    z-index: 10;
}

.search-suggestions.active {
    display: block;
}

.suggestion-item {
    padding: 12px 20px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
}

.suggestion-item:hover {
    background: #f8f8f8;
}

.suggestion-icon {
    width: 32px;
    height: 32px;
    background: #f0f0f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* Quick Filters */
.quick-filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 48px;
}

.filter-chip {
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-chip:hover {
    border-color: #000;
    color: #000;
    transform: translateY(-2px);
}

.filter-chip.active {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Main Content */
.ai-main-content {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 32px;
    margin-bottom: 48px;
}

/* AI Assistant Panel - Premium Design */
.ai-assistant-panel {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px;
    display: flex;
    flex-direction: column;
    height: 680px;
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
    position: relative;
    overflow: hidden;
}

.ai-assistant-panel::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.assistant-header {
    padding: 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 2;
}

.assistant-avatar {
    position: relative;
    width: 56px;
    height: 56px;
}

.avatar-ring {
    position: absolute;
    inset: -4px;
    border: 3px solid;
    border-image: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #667eea) 1;
    border-radius: 50%;
    animation: rotateGradient 3s infinite linear;
}

.avatar-ring::before {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    opacity: 0.5;
    filter: blur(10px);
    animation: pulse 2s infinite;
}

@keyframes rotateGradient {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.1); }
}

.avatar-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 50%;
    font-size: 18px;
    font-weight: 900;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.assistant-info {
    flex: 1;
}

.assistant-name {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #fff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.assistant-status {
    font-size: 12px;
    color: #a5f3fc;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
}

.assistant-status::before {
    content: '';
    width: 8px;
    height: 8px;
    background: #10f981;
    border-radius: 50%;
    animation: blink 1s infinite;
    box-shadow: 0 0 10px #10f981;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Chat Messages - Premium Style */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: rgba(255, 255, 255, 0.95);
    position: relative;
    z-index: 1;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 3px;
}

.message {
    display: flex;
    gap: 12px;
    animation: messageIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    position: relative;
}

@keyframes messageIn {
    from { 
        opacity: 0; 
        transform: translateY(20px) scale(0.9);
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1);
    }
}

.message-user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}

.message-ai .message-avatar {
    background: linear-gradient(135deg, #667eea, #764ba2);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.message-user .message-avatar {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    box-shadow: 0 2px 8px rgba(240, 147, 251, 0.3);
}

.message-bubble {
    max-width: 75%;
    padding: 14px 18px;
    border-radius: 20px;
    font-size: 14px;
    line-height: 1.7;
    position: relative;
    word-wrap: break-word;
}

.message-ai .message-bubble {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    border-bottom-left-radius: 4px;
}

.message-user .message-bubble {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    color: #212529;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-bottom-right-radius: 4px;
}

.message-bubble::before {
    content: '';
    position: absolute;
    bottom: 0;
    width: 0;
    height: 0;
}

.message-ai .message-bubble::before {
    left: -8px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 12px solid #667eea;
}

.message-user .message-bubble::before {
    right: -8px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 12px solid #e9ecef;
}

.message-timestamp {
    font-size: 10px;
    opacity: 0.7;
    margin-top: 4px;
    display: block;
}

/* Chat Input - Modern Style */
.chat-input-area {
    padding: 20px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    border-top: 1px solid rgba(102, 126, 234, 0.2);
    position: relative;
    backdrop-filter: blur(10px);
}

.typing-indicator {
    position: absolute;
    top: -24px;
    left: 20px;
    display: none;
    gap: 4px;
}

.typing-indicator.active {
    display: flex;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #999;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

@keyframes cursorBlink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}

.typing-cursor {
    color: #667eea;
    font-weight: 100;
    margin-left: 2px;
}

.chat-input {
    width: 100%;
    padding: 12px 48px 12px 16px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 24px;
    font-size: 13px;
    resize: none;
    outline: none;
    transition: all 0.2s;
}

.chat-input:focus {
    border-color: #000;
}

.chat-send-btn {
    position: absolute;
    right: 24px;
    bottom: 24px;
    width: 32px;
    height: 32px;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.chat-send-btn:hover {
    transform: scale(1.1);
}

.chat-send-btn:active {
    transform: scale(0.95);
}

/* Quick Questions */
.quick-questions {
    padding: 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.quick-q {
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.quick-q:hover {
    background: #000;
    color: #fff;
    border-color: #000;
}

/* Search Results Panel */
.search-results-panel {
    background: #fafafa;
    border-radius: 20px;
    padding: 24px;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.results-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

#results-count {
    font-size: 24px;
    font-weight: 900;
    color: #000;
}

.view-controls {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #fff;
    border-radius: 8px;
}

.view-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: none;
    color: #999;
    cursor: pointer;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.view-btn:hover {
    background: #f0f0f0;
}

.view-btn.active {
    background: #000;
    color: #fff;
}

.view-btn svg {
    fill: currentColor;
}

/* Grant Cards */
.featured-grants,
.results-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.grant-card {
    position: relative;
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
    cursor: pointer;
}

.grant-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    border-color: #000;
}

.card-badge {
    position: absolute;
    top: -8px;
    right: 16px;
    padding: 4px 12px;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    border-radius: 12px;
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 12px;
    line-height: 1.4;
}

.card-meta {
    display: flex;
    gap: 16px;
    margin-bottom: 12px;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 10px;
    color: #999;
    margin-bottom: 2px;
}

.meta-value {
    font-size: 14px;
    font-weight: 700;
    color: #000;
}

.card-org {
    font-size: 11px;
    color: #666;
    margin: 0 0 12px;
}

.card-rate {
    margin-bottom: 16px;
}

.rate-bar {
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 4px;
}

.rate-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s ease-out;
}

.rate-text {
    font-size: 10px;
    color: #666;
}

.card-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #000;
    text-decoration: none;
    transition: all 0.2s;
}

.card-link:hover {
    gap: 10px;
}

.card-link svg {
    stroke: currentColor;
    stroke-width: 2;
    fill: none;
}

/* Loading State */
.results-loading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px;
    color: #666;
    font-size: 14px;
}

.results-loading.active {
    display: flex;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f0f0f0;
    border-top-color: #000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Stats Bar */
.stats-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    padding: 32px;
    background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
    border-radius: 20px;
    text-align: center;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-number {
    font-size: 32px;
    font-weight: 900;
    color: #000;
    font-variant-numeric: tabular-nums;
}

.stat-label {
    font-size: 12px;
    color: #666;
    letter-spacing: 0.05em;
}

/* Notification System */
.ai-notification {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 16px;
    background: #333;
    color: #fff;
    border-radius: 20px;
    font-size: 12px;
    opacity: 0;
    transition: all 0.3s;
    z-index: 100;
    white-space: nowrap;
}

.ai-notification.visible {
    opacity: 1;
    top: -50px;
}

.ai-notification.error {
    background: #dc3545;
}

.ai-notification.success {
    background: #28a745;
}

.ai-notification.info {
    background: #17a2b8;
}

/* Voice Recording Animation */
.voice-btn.recording {
    background: #dc3545;
    color: #fff;
    animation: recordPulse 1.5s infinite;
}

@keyframes recordPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Loading States */
.search-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.search-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Enhanced Grant Cards */
.grant-card.loading {
    pointer-events: none;
    opacity: 0.5;
}

.grant-card .loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    display: none;
}

.grant-card.loading .loading-overlay {
    display: flex;
}

/* List View */
.results-list .grant-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
}

.results-list .card-title {
    flex: 1;
}

.results-list .card-meta {
    display: flex;
    gap: 20px;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-results h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.no-results p {
    font-size: 14px;
    color: #999;
}

/* Error Message */
.error-message {
    padding: 20px;
    background: #fee;
    color: #c33;
    border-radius: 8px;
    text-align: center;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 1024px) {
    .ai-main-content {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .ai-assistant-panel {
        height: 400px;
    }
}

@media (max-width: 768px) {
    .title-en {
        font-size: 32px;
    }
    
    .quick-filters {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    
    .stats-bar {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        padding: 20px;
    }
    
    .featured-grants,
    .results-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        API_URL: '<?php echo esc_url(admin_url("admin-ajax.php")); ?>',
        NONCE: '<?php echo esc_js($nonce); ?>',
        SESSION_ID: '<?php echo esc_js($session_id); ?>',
        TYPING_DELAY: 30,
        DEBOUNCE_DELAY: 300,
    };
    
    // Debug: AJAXのURLとnonceを確認
    console.log('AJAX Configuration:', {
        url: CONFIG.API_URL,
        nonce: CONFIG.NONCE,
        session: CONFIG.SESSION_ID
    });

    // AI Search Controller
    class AISearchController {
        constructor() {
            this.state = {
                isSearching: false,
                isTyping: false,
                currentFilter: 'all',
                currentView: 'grid',
                results: [],
                chatHistory: [],
            };
            
            this.elements = {};
            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            this.initAnimations();
            this.animateStats();
            this.testConnection(); // テスト接続
        }

        // AJAXテスト接続
        async testConnection() {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_test_connection');
                
                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                console.log('Test connection result:', data);
            } catch (error) {
                console.error('Test connection failed:', error);
            }
        }

        cacheElements() {
            this.elements = {
                searchInput: document.getElementById('ai-search-input'),
                searchBtn: document.getElementById('ai-search-btn'),
                suggestions: document.getElementById('search-suggestions'),
                filterChips: document.querySelectorAll('.filter-chip'),
                chatMessages: document.getElementById('chat-messages'),
                chatInput: document.getElementById('chat-input'),
                chatSend: document.getElementById('chat-send'),
                typingIndicator: document.getElementById('typing-indicator'),
                resultsContainer: document.getElementById('results-container'),
                resultsLoading: document.getElementById('results-loading'),
                resultsCount: document.getElementById('results-count'),
                viewBtns: document.querySelectorAll('.view-btn'),
                quickQuestions: document.querySelectorAll('.quick-q'),
                voiceBtn: document.querySelector('.voice-btn'),
            };
        }

        bindEvents() {
            // Search events
            this.elements.searchInput?.addEventListener('input', this.debounce(this.handleSearchInput.bind(this), CONFIG.DEBOUNCE_DELAY));
            this.elements.searchInput?.addEventListener('focus', this.showSuggestions.bind(this));
            this.elements.searchBtn?.addEventListener('click', this.performSearch.bind(this));
            
            // Enter key for search
            this.elements.searchInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });

            // Filter chips
            this.elements.filterChips.forEach(chip => {
                chip.addEventListener('click', this.handleFilterClick.bind(this));
            });

            // Chat events
            this.elements.chatInput?.addEventListener('input', this.autoResizeTextarea.bind(this));
            this.elements.chatSend?.addEventListener('click', this.sendChatMessage.bind(this));
            
            // Enter key for chat (Shift+Enter for new line)
            this.elements.chatInput?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendChatMessage();
                }
            });

            // Quick questions
            this.elements.quickQuestions.forEach(btn => {
                btn.addEventListener('click', this.handleQuickQuestion.bind(this));
            });

            // View controls
            this.elements.viewBtns.forEach(btn => {
                btn.addEventListener('click', this.handleViewChange.bind(this));
            });

            // Voice input
            this.elements.voiceBtn?.addEventListener('click', this.startVoiceInput.bind(this));

            // Click outside to close suggestions
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.ai-search-bar')) {
                    this.hideSuggestions();
                }
            });
        }

        // Search Methods
        async handleSearchInput(e) {
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideSuggestions();
                return;
            }

            // Get suggestions from server
            const suggestions = await this.fetchSuggestions(query);
            this.displaySuggestions(suggestions);
        }

        async fetchSuggestions(query) {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_search_suggestions');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('query', query);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.success && data.data.suggestions) {
                    return data.data.suggestions;
                }
            } catch (error) {
                console.error('Suggestions error:', error);
            }

            // Fallback suggestions
            return [
                { icon: '🏭', text: 'ものづくり補助金', type: 'grant' },
                { icon: '💻', text: 'IT導入補助金', type: 'grant' },
                { icon: '🏪', text: '小規模事業者持続化補助金', type: 'grant' },
                { icon: '🔄', text: '事業再構築補助金', type: 'grant' }
            ].filter(s => s.text.toLowerCase().includes(query.toLowerCase()));
        }

        displaySuggestions(suggestions) {
            const container = this.elements.suggestions;
            if (!container) return;

            if (suggestions.length === 0) {
                this.hideSuggestions();
                return;
            }

            container.innerHTML = suggestions.map(s => `
                <div class="suggestion-item" data-text="${s.text}">
                    <span class="suggestion-icon">${s.icon}</span>
                    <span>${s.text}</span>
                </div>
            `).join('');

            container.classList.add('active');

            // Bind click events
            container.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.elements.searchInput.value = item.dataset.text;
                    this.hideSuggestions();
                    this.performSearch();
                });
            });
        }

        showSuggestions() {
            if (this.elements.searchInput.value.length >= 2) {
                this.elements.suggestions?.classList.add('active');
            }
        }

        hideSuggestions() {
            this.elements.suggestions?.classList.remove('active');
        }

        async performSearch() {
            const query = this.elements.searchInput.value.trim();
            if (!query || this.state.isSearching) return;

            this.state.isSearching = true;
            this.showLoading();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_search');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('query', query);
                formData.append('filter', this.state.currentFilter);
                formData.append('session_id', CONFIG.SESSION_ID);

                // Debug: リクエストの詳細を表示
                console.log('Sending search request:', {
                    url: CONFIG.API_URL,
                    action: 'gi_ai_search',
                    nonce: CONFIG.NONCE,
                    query: query,
                    filter: this.state.currentFilter
                });

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    this.showError('サーバーからの応答が不正です: ' + text.substring(0, 100));
                    return;
                }

                if (data.success) {
                    this.displayResults(data.data.grants);
                    this.updateResultsCount(data.data.count);
                    
                    // Add AI response to chat
                    if (data.data.ai_response) {
                        this.addChatMessage(data.data.ai_response, 'ai');
                    }
                } else {
                    const errorMsg = data.data?.message || data.data || '検索エラーが発生しました';
                    console.error('Search failed:', errorMsg);
                    this.showError(errorMsg);
                }
            } catch (error) {
                console.error('Search error:', error);
                this.showError('通信エラーが発生しました: ' + error.message);
            } finally {
                this.state.isSearching = false;
                this.hideLoading();
            }
        }

        displayResults(grants) {
            const container = this.elements.resultsContainer;
            if (!container || !grants) return;

            if (grants.length === 0) {
                container.innerHTML = '<div class="no-results">該当する補助金が見つかりませんでした</div>';
                return;
            }

            container.innerHTML = grants.map(grant => this.createGrantCard(grant)).join('');
            this.animateCards();
        }

        createGrantCard(grant) {
            return `
                <div class="grant-card" data-id="${grant.id}" style="animation-delay: ${Math.random() * 0.2}s">
                    ${grant.featured ? '<div class="card-badge">注目</div>' : ''}
                    <h4 class="card-title">${grant.title}</h4>
                    <div class="card-meta">
                        <span class="meta-item">
                            <span class="meta-label">最大</span>
                            <span class="meta-value">${grant.amount || '未定'}</span>
                        </span>
                        <span class="meta-item">
                            <span class="meta-label">締切</span>
                            <span class="meta-value">${grant.deadline || '随時'}</span>
                        </span>
                    </div>
                    <p class="card-org">${grant.organization || ''}</p>
                    ${grant.success_rate ? `
                        <div class="card-rate">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width: ${grant.success_rate}%"></div>
                            </div>
                            <span class="rate-text">採択率 ${grant.success_rate}%</span>
                        </div>
                    ` : ''}
                    <a href="${grant.permalink}" class="card-link">
                        詳細を見る
                        <svg width="12" height="12" viewBox="0 0 12 12">
                            <path d="M2 6h8m0 0L7 3m3 3L7 9"/>
                        </svg>
                    </a>
                </div>
            `;
        }

        updateResultsCount(count) {
            if (this.elements.resultsCount) {
                this.animateNumber(this.elements.resultsCount, count);
            }
        }

        // Filter Methods
        handleFilterClick(e) {
            const filter = e.currentTarget.dataset.filter;
            
            // Update active state
            this.elements.filterChips.forEach(chip => {
                chip.classList.toggle('active', chip.dataset.filter === filter);
            });

            this.state.currentFilter = filter;
            
            // Perform search with new filter
            if (this.elements.searchInput.value) {
                this.performSearch();
            }
        }

        // Chat Methods
        async sendChatMessage() {
            const message = this.elements.chatInput.value.trim();
            if (!message || this.state.isTyping) return;

            // Clear input
            this.elements.chatInput.value = '';
            this.autoResizeTextarea();

            // Add user message
            this.addChatMessage(message, 'user');

            // Show typing indicator
            this.showTyping();

            try {
                const formData = new FormData();
                formData.append('action', 'gi_ai_chat');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('message', message);
                formData.append('session_id', CONFIG.SESSION_ID);

                const response = await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    this.addChatMessage('サーバーエラー: 不正な応答形式です。', 'ai');
                    return;
                }

                if (data.success) {
                    // Type AI response
                    this.typeMessage(data.data.response);
                    
                    // Update search results if needed
                    if (data.data.related_grants) {
                        this.displayResults(data.data.related_grants);
                    }
                } else {
                    const errorMsg = data.data?.message || data.data || '申し訳ございません。エラーが発生しました。';
                    console.error('Chat failed:', errorMsg);
                    this.addChatMessage(errorMsg, 'ai');
                }
            } catch (error) {
                console.error('Chat error:', error);
                this.addChatMessage('通信エラーが発生しました: ' + error.message, 'ai');
            } finally {
                this.hideTyping();
            }
        }

        addChatMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message message-${type}`;
            
            // アバター追加
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = type === 'user' ? 'You' : 'AI';
            messageDiv.appendChild(avatar);
            
            // メッセージバブル
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            
            // テキストを処理（改行やリンクなど）
            let processedText = text
                .replace(/\n/g, '<br>')
                .replace(/【([^】]+)】/g, '<strong>$1</strong>')
                .replace(/https?:\/\/[^\s]+/g, '<a href="$&" target="_blank" rel="noopener">$&</a>');
            
            bubble.innerHTML = processedText;
            
            // タイムスタンプ
            const timestamp = document.createElement('span');
            timestamp.className = 'message-timestamp';
            timestamp.textContent = new Date().toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
            bubble.appendChild(timestamp);
            
            messageDiv.appendChild(bubble);
            
            // アニメーション付きで追加
            messageDiv.style.opacity = '0';
            this.elements.chatMessages.appendChild(messageDiv);
            
            // フェードインアニメーション
            setTimeout(() => {
                messageDiv.style.transition = 'opacity 0.3s ease';
                messageDiv.style.opacity = '1';
            }, 10);
            
            this.scrollChatToBottom();
            
            // メッセージ追加時の効果音（オプション）
            this.playSound('message');
        }
        
        // サウンドエフェクト（オプション）
        playSound(type) {
            if (this.state.soundEnabled) {
                const audio = new Audio();
                switch(type) {
                    case 'message':
                        // メッセージ音のデータURI（軽量な音）
                        audio.src = 'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn...';
                        audio.volume = 0.3;
                        audio.play().catch(() => {});
                        break;
                }
            }
        }

        typeMessage(text, callback) {
            // AIアバターを作成
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message message-ai';
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = 'AI';
            messageDiv.appendChild(avatar);
            
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            messageDiv.appendChild(bubble);
            
            // タイムスタンプ
            const timestamp = document.createElement('span');
            timestamp.className = 'message-timestamp';
            timestamp.textContent = new Date().toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit' });
            
            this.elements.chatMessages.appendChild(messageDiv);
            
            // 高度なタイピングアニメーション
            let index = 0;
            const words = text.split(' ');
            let currentText = '';
            
            // カーソルエフェクト
            const cursor = document.createElement('span');
            cursor.className = 'typing-cursor';
            cursor.textContent = '▊';
            cursor.style.animation = 'cursorBlink 0.7s infinite';
            
            const typeWord = () => {
                if (index < text.length) {
                    // 特殊文字の処理
                    if (text[index] === '\n') {
                        currentText += '<br>';
                        bubble.innerHTML = currentText + cursor.outerHTML;
                    } else if (text[index] === '【') {
                        // 強調表示の開始
                        currentText += '<strong>';
                        bubble.innerHTML = currentText + cursor.outerHTML;
                    } else if (text[index] === '】') {
                        // 強調表示の終了
                        currentText += '</strong>';
                        bubble.innerHTML = currentText + cursor.outerHTML;
                    } else if (text[index] === '•') {
                        // リストアイテム
                        currentText += '<span style="color: #667eea;">•</span>';
                        bubble.innerHTML = currentText + cursor.outerHTML;
                    } else {
                        currentText += text[index];
                        bubble.innerHTML = currentText + cursor.outerHTML;
                    }
                    
                    index++;
                    this.scrollChatToBottom();
                    
                    // 可変速度タイピング（句読点で遅く、それ以外は速く）
                    let delay = CONFIG.TYPING_DELAY;
                    if (text[index - 1] === '。' || text[index - 1] === '、') {
                        delay = CONFIG.TYPING_DELAY * 3;
                    } else if (text[index - 1] === '\n') {
                        delay = CONFIG.TYPING_DELAY * 5;
                    }
                    
                    setTimeout(typeWord, delay);
                } else {
                    // タイピング完了
                    bubble.innerHTML = currentText;
                    bubble.appendChild(timestamp);
                    
                    // コールバック実行
                    if (callback) callback();
                }
            };
            
            typeWord();
        }

        handleQuickQuestion(e) {
            const question = e.currentTarget.dataset.q;
            this.elements.chatInput.value = question;
            this.autoResizeTextarea();
            this.sendChatMessage();
        }

        autoResizeTextarea() {
            const textarea = this.elements.chatInput;
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        scrollChatToBottom() {
            this.elements.chatMessages.scrollTop = this.elements.chatMessages.scrollHeight;
        }

        showTyping() {
            this.state.isTyping = true;
            this.elements.typingIndicator?.classList.add('active');
        }

        hideTyping() {
            this.state.isTyping = false;
            this.elements.typingIndicator?.classList.remove('active');
        }

        // View Methods
        handleViewChange(e) {
            const view = e.currentTarget.dataset.view;
            
            this.elements.viewBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.view === view);
            });

            this.state.currentView = view;
            
            // Update results display
            const container = this.elements.resultsContainer;
            if (container) {
                container.className = view === 'list' ? 'results-list' : 'featured-grants';
            }
        }

        // Voice Input
        startVoiceInput() {
            // Check for speech recognition support
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                this.showNotification('音声入力はこのブラウザではサポートされていません', 'error');
                return;
            }

            const recognition = new SpeechRecognition();
            recognition.lang = 'ja-JP';
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            recognition.continuous = false;

            // Visual feedback
            this.elements.voiceBtn?.classList.add('recording');
            this.showNotification('音声入力中...話してください', 'info');

            recognition.onstart = () => {
                console.log('Voice recognition started');
            };

            recognition.onresult = async (event) => {
                const transcript = Array.from(event.results)
                    .map(result => result[0])
                    .map(result => result.transcript)
                    .join('');
                
                this.elements.searchInput.value = transcript;
                
                // If final result, perform search
                if (event.results[event.results.length - 1].isFinal) {
                    this.hideNotification();
                    this.performSearch();
                    
                    // Save voice input history
                    if (transcript) {
                        this.saveVoiceHistory(transcript, event.results[0][0].confidence);
                    }
                }
            };

            recognition.onerror = (event) => {
                console.error('Voice recognition error:', event.error);
                let errorMessage = '音声認識エラーが発生しました';
                
                switch(event.error) {
                    case 'no-speech':
                        errorMessage = '音声が検出されませんでした';
                        break;
                    case 'audio-capture':
                        errorMessage = 'マイクが使用できません';
                        break;
                    case 'not-allowed':
                        errorMessage = 'マイクのアクセスが拒否されました';
                        break;
                }
                
                this.showNotification(errorMessage, 'error');
            };

            recognition.onend = () => {
                this.elements.voiceBtn?.classList.remove('recording');
                this.hideNotification();
            };

            recognition.start();
        }

        // Save voice input history
        async saveVoiceHistory(text, confidence) {
            try {
                const formData = new FormData();
                formData.append('action', 'gi_voice_history');
                formData.append('nonce', CONFIG.NONCE);
                formData.append('session_id', CONFIG.SESSION_ID);
                formData.append('text', text);
                formData.append('confidence', confidence);

                await fetch(CONFIG.API_URL, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
            } catch (error) {
                console.error('Voice history save error:', error);
            }
        }

        // Notification system
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `ai-notification ${type}`;
            notification.textContent = message;
            
            const container = document.querySelector('.ai-search-bar');
            container?.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('visible');
            }, 10);
        }

        hideNotification() {
            const notification = document.querySelector('.ai-notification');
            if (notification) {
                notification.classList.remove('visible');
                setTimeout(() => notification.remove(), 300);
            }
        }

        // Loading States
        showLoading() {
            this.elements.resultsLoading?.classList.add('active');
            this.elements.resultsContainer?.classList.add('loading');
        }

        hideLoading() {
            this.elements.resultsLoading?.classList.remove('active');
            this.elements.resultsContainer?.classList.remove('loading');
        }

        showError(message) {
            const container = this.elements.resultsContainer;
            if (container) {
                container.innerHTML = `<div class="error-message">${message}</div>`;
            }
        }

        // Animation Methods
        initAnimations() {
            // Intersection Observer for scroll animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.grant-card').forEach(card => {
                observer.observe(card);
            });
        }

        animateCards() {
            const cards = document.querySelectorAll('.grant-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }

        animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number[data-count]');
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.dataset.count);
                this.animateNumber(stat, target);
            });
        }

        animateNumber(element, target) {
            const duration = 1500;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // Utility Methods
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new AISearchController();
        });
    } else {
        new AISearchController();
    }

})();
</script>

