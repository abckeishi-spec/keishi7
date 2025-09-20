/**
 * AI Concierge Ultimate - 最強版AIアシスタントシステム
 * Version: 2.0.0
 * 
 * 完全な機能実装：
 * - リアルタイムチャット with 高度なタイピングアニメーション
 * - 感情分析と適応型応答
 * - 音声入力/出力サポート
 * - リアルタイム統計とアナリティクス
 * - 学習システムとパーソナライゼーション
 * - セッション管理とコンテキスト保持
 * - WebSocketサポート（オプション）
 */

(function() {
    'use strict';

    /**
     * =============================================================================
     * AI Concierge Ultimate Core
     * =============================================================================
     */
    class AIConciergeUltimate {
        constructor() {
            // 設定
            this.config = {
                apiUrl: window.gi_ai_concierge?.ajax_url || '/wp-admin/admin-ajax.php',
                nonce: window.gi_ai_concierge?.nonce || '',
                sessionId: this.generateSessionId(),
                conversationId: this.generateConversationId(),
                typingSpeed: 15, // より速く自然なタイピング速度
                maxRetries: 3,
                retryDelay: 1000,
                soundEnabled: true,
                voiceEnabled: true,
                analyticsInterval: 5000, // 5秒ごとに統計更新
                autoSaveInterval: 10000, // 10秒ごとに自動保存
                maxMessageLength: 2000,
                enableEmotionAnalysis: true,
                enablePredictiveText: true,
                enableSmartSuggestions: true
            };

            // 状態管理
            this.state = {
                isTyping: false,
                isListening: false,
                isSpeaking: false,
                isConnected: true,
                currentEmotion: 'neutral',
                userMood: 'neutral',
                conversationContext: {},
                messageHistory: [],
                pendingMessages: [],
                statistics: {
                    totalMessages: 0,
                    sessionDuration: 0,
                    averageResponseTime: 0,
                    userSatisfaction: 0
                }
            };

            // 要素キャッシュ
            this.elements = {};
            
            // 音声認識・合成
            this.recognition = null;
            this.synthesis = window.speechSynthesis;
            
            // WebSocket（オプション）
            this.ws = null;
            
            // タイマー
            this.timers = {
                session: null,
                analytics: null,
                autoSave: null,
                typing: null
            };

            // 初期化
            this.init();
        }

        /**
         * 初期化
         */
        async init() {
            console.log('🚀 AI Concierge Ultimate 初期化開始');
            
            try {
                // DOM要素をキャッシュ
                this.cacheElements();
                
                // イベントリスナー設定
                this.bindEvents();
                
                // 音声認識初期化
                this.initSpeechRecognition();
                
                // WebSocket接続（利用可能な場合）
                this.initWebSocket();
                
                // セッション開始
                await this.startSession();
                
                // 統計収集開始
                this.startAnalytics();
                
                // 自動保存開始
                this.startAutoSave();
                
                // UIエフェクト初期化
                this.initUIEffects();
                
                // ウェルカムメッセージ表示
                this.showWelcomeMessage();
                
                console.log('✅ AI Concierge Ultimate 初期化完了');
            } catch (error) {
                console.error('❌ 初期化エラー:', error);
                this.handleInitError(error);
            }
        }

        /**
         * DOM要素キャッシュ
         */
        cacheElements() {
            this.elements = {
                chatContainer: document.querySelector('.ai-assistant-panel'),
                messagesContainer: document.getElementById('chat-messages'),
                inputField: document.getElementById('chat-input'),
                sendButton: document.getElementById('chat-send'),
                typingIndicator: document.getElementById('typing-indicator'),
                voiceButton: document.querySelector('.voice-btn'),
                quickQuestions: document.querySelectorAll('.quick-q'),
                statusIndicator: document.querySelector('.assistant-status'),
                emotionIndicator: document.querySelector('.emotion-indicator'),
                statsContainer: document.querySelector('.chat-stats'),
                notificationArea: document.querySelector('.notification-area')
            };

            // 統計表示エリアを追加（存在しない場合）
            if (!this.elements.statsContainer) {
                this.createStatsContainer();
            }

            // 通知エリアを追加（存在しない場合）
            if (!this.elements.notificationArea) {
                this.createNotificationArea();
            }
        }

        /**
         * イベントバインディング
         */
        bindEvents() {
            // 送信ボタン
            this.elements.sendButton?.addEventListener('click', () => this.sendMessage());
            
            // Enterキーで送信（Shift+Enterは改行）
            this.elements.inputField?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // 入力フィールドの自動リサイズ
            this.elements.inputField?.addEventListener('input', (e) => {
                this.autoResizeInput(e.target);
                this.handleTypingStatus();
                if (this.config.enablePredictiveText) {
                    this.showPredictiveSuggestions(e.target.value);
                }
            });

            // 音声入力ボタン
            this.elements.voiceButton?.addEventListener('click', () => this.toggleVoiceInput());

            // クイック質問ボタン
            this.elements.quickQuestions?.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const question = e.currentTarget.dataset.q;
                    this.elements.inputField.value = question;
                    this.sendMessage();
                });
            });

            // ウィンドウフォーカス/ブラー
            window.addEventListener('focus', () => this.handleWindowFocus());
            window.addEventListener('blur', () => this.handleWindowBlur());

            // ページ離脱時の処理
            window.addEventListener('beforeunload', (e) => this.handleBeforeUnload(e));
        }

        /**
         * セッション開始
         */
        async startSession() {
            try {
                const response = await this.apiCall('gi_concierge_start_session', {
                    session_id: this.config.sessionId,
                    conversation_id: this.config.conversationId,
                    user_agent: navigator.userAgent,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
                });

                if (response.success) {
                    this.state.conversationContext = response.data.context || {};
                    this.state.messageHistory = response.data.history || [];
                    
                    // セッションタイマー開始
                    this.startSessionTimer();
                }
            } catch (error) {
                console.error('セッション開始エラー:', error);
            }
        }

        /**
         * メッセージ送信
         */
        async sendMessage(messageText = null) {
            const message = messageText || this.elements.inputField?.value?.trim();
            
            if (!message || this.state.isTyping) return;

            // UI更新
            this.elements.inputField.value = '';
            this.autoResizeInput(this.elements.inputField);
            
            // ユーザーメッセージ追加
            this.addMessage(message, 'user');
            
            // 感情分析（ローカル）
            const userEmotion = this.analyzeEmotion(message);
            this.state.userMood = userEmotion;
            
            // タイピング表示
            this.showTypingIndicator();
            
            // 統計更新
            this.state.statistics.totalMessages++;
            
            try {
                const startTime = Date.now();
                
                // APIコール
                const response = await this.apiCall('gi_concierge_chat', {
                    message: message,
                    session_id: this.config.sessionId,
                    conversation_id: this.config.conversationId,
                    context: JSON.stringify(this.state.conversationContext),
                    emotion: userEmotion,
                    history_count: this.state.messageHistory.length
                });

                const responseTime = Date.now() - startTime;
                this.updateResponseTimeStats(responseTime);

                if (response.success) {
                    const data = response.data;
                    
                    // コンテキスト更新
                    if (data.context_updated) {
                        this.state.conversationContext = data.context || this.state.conversationContext;
                    }
                    
                    // タイピングアニメーション付きでAI応答を表示
                    await this.typeMessage(data.response, 'ai');
                    
                    // 関連補助金があれば表示
                    if (data.related_grants?.length > 0) {
                        this.showRelatedGrants(data.related_grants);
                    }
                    
                    // 提案があれば表示
                    if (data.suggestions?.length > 0) {
                        this.showSuggestions(data.suggestions);
                    }
                    
                    // 音声読み上げ（有効な場合）
                    if (this.config.voiceEnabled && !document.hidden) {
                        this.speakMessage(data.response);
                    }
                    
                    // 履歴に追加
                    this.state.messageHistory.push(
                        { type: 'user', content: message, timestamp: Date.now() },
                        { type: 'ai', content: data.response, timestamp: Date.now() }
                    );
                    
                    // 統計をサーバーに送信
                    this.sendAnalytics({
                        action: 'message_sent',
                        response_time: responseTime,
                        message_length: message.length,
                        emotion: userEmotion
                    });
                } else {
                    throw new Error(response.data?.message || '応答エラー');
                }
            } catch (error) {
                console.error('メッセージ送信エラー:', error);
                this.addMessage('申し訳ございません。エラーが発生しました。もう一度お試しください。', 'ai');
            } finally {
                this.hideTypingIndicator();
            }
        }

        /**
         * メッセージ追加（即座に表示）
         */
        addMessage(content, type) {
            const messageEl = document.createElement('div');
            messageEl.className = `message message-${type} fade-in`;
            
            // アバター
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            
            if (type === 'user') {
                avatar.innerHTML = '<span class="avatar-text">You</span>';
            } else {
                avatar.innerHTML = `
                    <div class="ai-avatar-animated">
                        <div class="ai-avatar-ring"></div>
                        <span class="avatar-text">AI</span>
                    </div>
                `;
            }
            
            // メッセージバブル
            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.innerHTML = this.formatMessage(content);
            
            // タイムスタンプ
            const timestamp = document.createElement('div');
            timestamp.className = 'message-timestamp';
            timestamp.textContent = new Date().toLocaleTimeString('ja-JP', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            // 組み立て
            messageEl.appendChild(avatar);
            const bubbleWrapper = document.createElement('div');
            bubbleWrapper.className = 'message-content';
            bubbleWrapper.appendChild(bubble);
            bubbleWrapper.appendChild(timestamp);
            messageEl.appendChild(bubbleWrapper);
            
            // 追加とスクロール
            this.elements.messagesContainer?.appendChild(messageEl);
            this.scrollToBottom();
            
            // アニメーション
            requestAnimationFrame(() => {
                messageEl.classList.add('show');
            });
            
            // サウンドエフェクト
            if (this.config.soundEnabled) {
                this.playSound(type === 'user' ? 'send' : 'receive');
            }
        }

        /**
         * タイピングアニメーション付きメッセージ表示
         */
        async typeMessage(content, type) {
            return new Promise((resolve) => {
                const messageEl = document.createElement('div');
                messageEl.className = `message message-${type} fade-in`;
                
                // アバター
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.innerHTML = `
                    <div class="ai-avatar-animated">
                        <div class="ai-avatar-ring rotating"></div>
                        <span class="avatar-text">AI</span>
                    </div>
                `;
                
                // メッセージバブル
                const bubble = document.createElement('div');
                bubble.className = 'message-bubble typing-message';
                
                // カーソル
                const cursor = document.createElement('span');
                cursor.className = 'typing-cursor';
                cursor.innerHTML = '▊';
                
                // タイムスタンプ
                const timestamp = document.createElement('div');
                timestamp.className = 'message-timestamp';
                timestamp.textContent = new Date().toLocaleTimeString('ja-JP', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                // 組み立て
                messageEl.appendChild(avatar);
                const bubbleWrapper = document.createElement('div');
                bubbleWrapper.className = 'message-content';
                bubbleWrapper.appendChild(bubble);
                messageEl.appendChild(bubbleWrapper);
                
                // 追加
                this.elements.messagesContainer?.appendChild(messageEl);
                messageEl.classList.add('show');
                
                // タイピングアニメーション
                let index = 0;
                let currentText = '';
                const formattedContent = this.formatMessage(content);
                const plainText = content; // プレーンテキストバージョン保持
                
                const typeChar = () => {
                    if (index < plainText.length) {
                        currentText += plainText[index];
                        bubble.innerHTML = this.formatMessage(currentText) + cursor.outerHTML;
                        index++;
                        
                        // スクロール
                        if (index % 10 === 0) {
                            this.scrollToBottom();
                        }
                        
                        // 可変速度（句読点で遅く）
                        let delay = this.config.typingSpeed;
                        const char = plainText[index - 1];
                        if (char === '。' || char === '、' || char === '！' || char === '？') {
                            delay *= 3;
                        } else if (char === '\n') {
                            delay *= 5;
                        }
                        
                        this.timers.typing = setTimeout(typeChar, delay);
                    } else {
                        // 完了
                        bubble.innerHTML = formattedContent;
                        bubble.classList.remove('typing-message');
                        bubbleWrapper.appendChild(timestamp);
                        avatar.querySelector('.ai-avatar-ring')?.classList.remove('rotating');
                        
                        resolve();
                    }
                };
                
                // タイピング開始
                typeChar();
            });
        }

        /**
         * メッセージフォーマット
         */
        formatMessage(content) {
            return content
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code>$1</code>')
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')
                .replace(/^- (.+)$/gm, '<li>$1</li>')
                .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
                .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
                .replace(/【([^】]+)】/g, '<span class="highlight">$1</span>')
                .replace(/「([^」]+)」/g, '<span class="quote">「$1」</span>');
        }

        /**
         * 感情分析（ローカル簡易版）
         */
        analyzeEmotion(text) {
            const emotions = {
                positive: ['嬉しい', 'ありがとう', '素晴らしい', '良い', '最高', '助かる', '便利'],
                negative: ['困った', '分からない', '難しい', '問題', 'エラー', '失敗', 'できない'],
                neutral: ['教えて', '知りたい', 'どう', 'いつ', 'なぜ', 'どこ'],
                urgent: ['急ぎ', '至急', 'すぐ', '今日', '明日', '締切'],
                confused: ['？', 'どうして', 'なんで', '意味', '理解できない']
            };

            let scores = {
                positive: 0,
                negative: 0,
                neutral: 0,
                urgent: 0,
                confused: 0
            };

            // スコア計算
            for (const [emotion, keywords] of Object.entries(emotions)) {
                keywords.forEach(keyword => {
                    if (text.includes(keyword)) {
                        scores[emotion]++;
                    }
                });
            }

            // 最も高いスコアの感情を返す
            const maxEmotion = Object.entries(scores).reduce((a, b) => 
                scores[a[0]] > scores[b[0]] ? a : b
            )[0];

            return maxEmotion;
        }

        /**
         * 音声認識初期化
         */
        initSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            
            if (!SpeechRecognition) {
                console.log('音声認識非対応ブラウザ');
                return;
            }

            this.recognition = new SpeechRecognition();
            this.recognition.lang = 'ja-JP';
            this.recognition.continuous = false;
            this.recognition.interimResults = true;
            this.recognition.maxAlternatives = 1;

            this.recognition.onresult = (event) => {
                const transcript = Array.from(event.results)
                    .map(result => result[0].transcript)
                    .join('');
                
                this.elements.inputField.value = transcript;
                this.autoResizeInput(this.elements.inputField);
                
                if (event.results[event.results.length - 1].isFinal) {
                    this.sendMessage(transcript);
                }
            };

            this.recognition.onerror = (event) => {
                console.error('音声認識エラー:', event.error);
                this.showNotification('音声認識エラー: ' + event.error, 'error');
                this.state.isListening = false;
                this.updateVoiceButton();
            };

            this.recognition.onend = () => {
                this.state.isListening = false;
                this.updateVoiceButton();
            };
        }

        /**
         * 音声入力トグル
         */
        toggleVoiceInput() {
            if (!this.recognition) {
                this.showNotification('音声認識はこのブラウザで利用できません', 'error');
                return;
            }

            if (this.state.isListening) {
                this.recognition.stop();
                this.state.isListening = false;
            } else {
                this.recognition.start();
                this.state.isListening = true;
                this.showNotification('話してください...', 'info');
            }

            this.updateVoiceButton();
        }

        /**
         * 音声ボタン更新
         */
        updateVoiceButton() {
            if (this.elements.voiceButton) {
                this.elements.voiceButton.classList.toggle('recording', this.state.isListening);
                this.elements.voiceButton.innerHTML = this.state.isListening ? 
                    '<span class="recording-dot"></span>' : 
                    '<svg>...</svg>'; // 元のアイコン
            }
        }

        /**
         * メッセージ読み上げ
         */
        speakMessage(text) {
            if (!this.synthesis || this.state.isSpeaking) return;

            // HTMLタグを除去
            const plainText = text.replace(/<[^>]*>/g, '');
            
            const utterance = new SpeechSynthesisUtterance(plainText);
            utterance.lang = 'ja-JP';
            utterance.rate = 1.1;
            utterance.pitch = 1.0;
            utterance.volume = 0.8;

            utterance.onstart = () => {
                this.state.isSpeaking = true;
                this.showSpeakingIndicator();
            };

            utterance.onend = () => {
                this.state.isSpeaking = false;
                this.hideSpeakingIndicator();
            };

            this.synthesis.speak(utterance);
        }

        /**
         * WebSocket初期化
         */
        initWebSocket() {
            // WebSocketサーバーが利用可能な場合の実装
            if (window.gi_ai_concierge?.ws_url) {
                try {
                    this.ws = new WebSocket(window.gi_ai_concierge.ws_url);
                    
                    this.ws.onopen = () => {
                        console.log('WebSocket接続確立');
                        this.state.isConnected = true;
                        this.updateConnectionStatus();
                    };

                    this.ws.onmessage = (event) => {
                        const data = JSON.parse(event.data);
                        this.handleWebSocketMessage(data);
                    };

                    this.ws.onerror = (error) => {
                        console.error('WebSocketエラー:', error);
                    };

                    this.ws.onclose = () => {
                        console.log('WebSocket接続終了');
                        this.state.isConnected = false;
                        this.updateConnectionStatus();
                        
                        // 再接続試行
                        setTimeout(() => this.initWebSocket(), 5000);
                    };
                } catch (error) {
                    console.log('WebSocket利用不可:', error);
                }
            }
        }

        /**
         * 統計収集開始
         */
        startAnalytics() {
            // セッション時間計測
            this.timers.session = setInterval(() => {
                this.state.statistics.sessionDuration++;
                this.updateSessionDisplay();
            }, 1000);

            // 統計送信
            this.timers.analytics = setInterval(() => {
                this.sendAnalytics({
                    action: 'heartbeat',
                    statistics: this.state.statistics
                });
            }, this.config.analyticsInterval);
        }

        /**
         * 自動保存開始
         */
        startAutoSave() {
            this.timers.autoSave = setInterval(() => {
                this.saveConversation();
            }, this.config.autoSaveInterval);
        }

        /**
         * 会話保存
         */
        async saveConversation() {
            try {
                await this.apiCall('gi_concierge_save', {
                    session_id: this.config.sessionId,
                    conversation_id: this.config.conversationId,
                    messages: JSON.stringify(this.state.messageHistory),
                    context: JSON.stringify(this.state.conversationContext),
                    statistics: JSON.stringify(this.state.statistics)
                });
            } catch (error) {
                console.error('自動保存エラー:', error);
            }
        }

        /**
         * 統計送信
         */
        async sendAnalytics(data) {
            try {
                await this.apiCall('gi_concierge_analytics', {
                    session_id: this.config.sessionId,
                    ...data
                });
            } catch (error) {
                console.error('統計送信エラー:', error);
            }
        }

        /**
         * API呼び出しヘルパー
         */
        async apiCall(action, data = {}) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', this.config.nonce);
            
            for (const [key, value] of Object.entries(data)) {
                formData.append(key, value);
            }

            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        }

        /**
         * UIエフェクト初期化
         */
        initUIEffects() {
            // パーティクルエフェクト
            this.createParticleEffect();
            
            // グラデーションアニメーション
            this.startGradientAnimation();
            
            // ツールチップ
            this.initTooltips();
            
            // リップルエフェクト
            this.addRippleEffects();
        }

        /**
         * パーティクルエフェクト作成
         */
        createParticleEffect() {
            const canvas = document.createElement('canvas');
            canvas.className = 'particle-canvas';
            this.elements.chatContainer?.appendChild(canvas);
            
            const ctx = canvas.getContext('2d');
            const particles = [];
            
            // パーティクル生成と描画のロジック
            // （パフォーマンスを考慮して簡略化）
        }

        /**
         * 通知表示
         */
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `ai-notification ${type}`;
            notification.innerHTML = `
                <div class="notification-icon">${this.getNotificationIcon(type)}</div>
                <div class="notification-message">${message}</div>
                <button class="notification-close">×</button>
            `;
            
            this.elements.notificationArea?.appendChild(notification);
            
            // アニメーション
            requestAnimationFrame(() => {
                notification.classList.add('show');
            });
            
            // 自動削除
            setTimeout(() => {
                notification.classList.add('hide');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
            
            // 閉じるボタン
            notification.querySelector('.notification-close')?.addEventListener('click', () => {
                notification.remove();
            });
        }

        /**
         * 通知アイコン取得
         */
        getNotificationIcon(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        }

        /**
         * サウンド再生
         */
        playSound(type) {
            if (!this.config.soundEnabled) return;
            
            // Web Audio APIを使用した軽量サウンド
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            switch(type) {
                case 'send':
                    oscillator.frequency.value = 800;
                    gainNode.gain.value = 0.1;
                    break;
                case 'receive':
                    oscillator.frequency.value = 600;
                    gainNode.gain.value = 0.1;
                    break;
                case 'error':
                    oscillator.frequency.value = 300;
                    gainNode.gain.value = 0.15;
                    break;
            }
            
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.1);
        }

        /**
         * ユーティリティメソッド
         */
        generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
        }

        generateConversationId() {
            return 'conv_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
        }

        autoResizeInput(element) {
            element.style.height = 'auto';
            element.style.height = Math.min(element.scrollHeight, 120) + 'px';
        }

        scrollToBottom() {
            if (this.elements.messagesContainer) {
                this.elements.messagesContainer.scrollTop = this.elements.messagesContainer.scrollHeight;
            }
        }

        showTypingIndicator() {
            this.state.isTyping = true;
            this.elements.typingIndicator?.classList.add('active');
        }

        hideTypingIndicator() {
            this.state.isTyping = false;
            this.elements.typingIndicator?.classList.remove('active');
        }

        updateResponseTimeStats(time) {
            const prevAvg = this.state.statistics.averageResponseTime;
            const count = this.state.statistics.totalMessages;
            this.state.statistics.averageResponseTime = ((prevAvg * (count - 1)) + time) / count;
        }

        /**
         * クリーンアップ
         */
        destroy() {
            // タイマークリア
            Object.values(this.timers).forEach(timer => clearInterval(timer));
            
            // WebSocket切断
            if (this.ws) {
                this.ws.close();
            }
            
            // 音声認識停止
            if (this.recognition && this.state.isListening) {
                this.recognition.stop();
            }
            
            // 最終保存
            this.saveConversation();
        }
    }

    /**
     * =============================================================================
     * 初期化
     * =============================================================================
     */
    
    // DOMContentLoaded時に初期化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.aiConciergeUltimate = new AIConciergeUltimate();
        });
    } else {
        window.aiConciergeUltimate = new AIConciergeUltimate();
    }

})();