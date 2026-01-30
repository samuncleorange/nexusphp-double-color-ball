@extends('dcb::layouts.app')

@section('title', nexus_trans('dcb::dcb.labels.fairness_verify'))

@section('content')
<div class="piggo-card animate-float" style="max-width: 1000px; margin: 0 auto; margin-bottom: 30px;">
    <div class="page-header">
        <div style="font-size: 4em;">🔐</div>
        <h1 class="page-title">{{ nexus_trans('dcb::dcb.labels.fairness_verify') }}</h1>
        <p class="page-subtitle">{{ nexus_trans('dcb::dcb.help.fairness_intro') }}</p>
    </div>

    <div class="dcb-nav" style="justify-content: center; width: 100%; box-sizing: border-box;">
        <a href="{{ route('dcb.index') }}">← {{ nexus_trans('dcb::dcb.buttons.buy_now') }}</a>
        <a href="{{ route('dcb.my-tickets') }}">{{ nexus_trans('dcb::dcb.buttons.view_my_tickets') }}</a>
        <a href="{{ route('dcb.history') }}">{{ nexus_trans('dcb::dcb.buttons.view_history') }}</a>
    </div>
</div>

<div class="piggo-card" style="max-width: 800px; margin: 0 auto; margin-bottom: 30px; background: #e3f2fd; border: 2px solid #90caf9;">
    <h3 style="color: var(--piggo-blue); margin-top: 0;">💡 {{ nexus_trans('dcb::dcb.help.how_to_verify_text') }}</h3>
    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #555;">
        <li>每期开奖使用开奖时刻后产生的第一个<strong>比特币区块哈希</strong>作为随机种子。</li>
        <li>通过 <code>HMAC-SHA512</code> 算法结合期号生成确定性随机数。</li>
        <li>任何人都可以使用相同的区块哈希和期号重新计算，结果必然一致。</li>
        <li>比特币区块由全球算力生成，系统无法预测或操纵。</li>
    </ul>
</div>

<div class="piggo-card" style="max-width: 800px; margin: 0 auto;">
    <h3 style="text-align: center; margin-bottom: 20px; color: #666;">📝 输入期号进行验证</h3>
    <form id="verifyForm" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" id="period_code" name="period_code" placeholder="例如: {{ date('Ymd') }}01" required 
               style="flex: 1; padding: 12px 20px; border-radius: 50px; border: 2px solid #ddd; font-size: 1.1em; outline: none; transition: border-color 0.3s;">
        <button type="submit" class="piggo-btn btn-primary" id="verifyBtn" style="white-space: nowrap;">
            🔎 {{ nexus_trans('dcb::dcb.buttons.verify') }}
        </button>
    </form>
</div>

<div id="resultBox" class="result-box" style="display: none; max-width: 800px; margin: 30px auto;">
    <!-- 结果将通过 JavaScript 动态填充 -->
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('verifyForm');
    const resultBox = document.getElementById('resultBox');
    const verifyBtn = document.getElementById('verifyBtn');
    const inputField = document.getElementById('period_code');

    // Add focus style manually if needed or handle via CSS, here using inline styles on input above.
    inputField.addEventListener('focus', () => inputField.style.borderColor = 'var(--piggo-blue)');
    inputField.addEventListener('blur', () => inputField.style.borderColor = '#ddd');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const periodCode = inputField.value.trim();
        
        if (!periodCode) {
            window.DCB.showToast('请输入期号', 'error');
            return;
        }

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '🔄 验证中...';
        
        // Improve loading UX
        resultBox.style.display = 'block';
        resultBox.innerHTML = `
            <div class="piggo-card" style="text-align: center; padding: 40px;">
                <div class="animate-bounce" style="font-size: 3em;">🐷</div>
                <p>正在连接区块链数据进行验证...</p>
            </div>
        `;

        try {
            const response = await fetch('{{ route('dcb.do-verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ period_code: periodCode })
            });

            const data = await response.json();

            if (data.success) {
                displayResult(data);
                window.DCB.showToast('验证完成', 'success');
            } else {
                showError(data.message || '验证失败');
                window.DCB.showToast(data.message || '验证失败', 'error');
            }
        } catch (error) {
            showError('网络错误，请重试');
            window.DCB.showToast('网络错误，请重试', 'error');
        } finally {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '🔎 {{ nexus_trans('dcb::dcb.buttons.verify') }}';
        }
    });

    function displayResult(data) {
        const isValid = data.is_valid;
        const period = data.period;
        const generated = data.generated;

        const html = `
            <div class="piggo-card animate-float" style="border: 3px solid ${isValid ? 'var(--piggo-green)' : 'var(--dcb-red)'};">
                <div style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px dashed #eee;">
                    <div style="font-size: 1.5em; font-weight: 900; color: ${isValid ? 'var(--piggo-green)' : 'var(--dcb-red)'};">
                        ${isValid ? '✅ 验证通过 (PASSED)' : '❌ 验证失败 (FAILED)'}
                    </div>
                    <div style="color: #666; margin-top: 5px;">
                        ${isValid ? '计算结果与实际开奖完全一致，公平公正！' : '计算结果不一致，请检查数据。'}
                    </div>
                </div>

                <div style="background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 20px; font-family: monospace; font-size: 0.9em; word-break: break-all;">
                    <div style="margin-bottom: 10px;"><strong>期号:</strong> ${period.code}</div>
                    <div style="margin-bottom: 10px;"><strong>区块高度:</strong> #${period.block_height}</div>
                    <div><strong>区块哈希:</strong> ${period.block_hash}</div>
                </div>

                <div class="comparison-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div class="comparison-item" style="background: #e8f5e9; border: 2px solid #4caf50; border-radius: 10px; padding: 15px;">
                        <h4 style="margin: 0 0 10px 0; color: #2e7d32; text-align: center;">📋 实际开奖</h4>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                            ${period.red_balls.map(ball => `<div class="dcb-ball red" style="width:35px; height:35px; font-size: 0.9em;">${ball}</div>`).join('')}
                            ${period.blue_balls.map(ball => `<div class="dcb-ball blue" style="width:35px; height:35px; font-size: 0.9em;">${ball}</div>`).join('')}
                        </div>
                    </div>

                    <div class="comparison-item" style="background: #fff3e0; border: 2px solid #ff9800; border-radius: 10px; padding: 15px;">
                        <h4 style="margin: 0 0 10px 0; color: #ef6c00; text-align: center;">🔢 重新计算</h4>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                            ${generated.red.map(ball => `<div class="dcb-ball red" style="width:35px; height:35px; font-size: 0.9em;">${ball}</div>`).join('')}
                            ${generated.blue.map(ball => `<div class="dcb-ball blue" style="width:35px; height:35px; font-size: 0.9em;">${ball}</div>`).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;

        resultBox.innerHTML = html;
        resultBox.style.display = 'block';
    }

    function showError(message) {
        resultBox.innerHTML = `
            <div class="piggo-card" style="background: #ffebee; border: 2px solid #ef5350; text-align: center;">
                <h3 style="color: #c62828;">⚠️ 错误</h3>
                <p>${message}</p>
            </div>
        `;
        resultBox.style.display = 'block';
    }
</script>
@endsection
