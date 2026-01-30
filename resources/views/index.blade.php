@extends('dcb::layouts.app')

@section('title', '双色球')

@section('content')
<div class="piggo-card animate-float" style="max-width: 1000px; margin: 0 auto;">
    <div class="page-header">
        <div style="font-size: 5em;">🐷</div>
        <h1 class="page-title">双色球</h1>
        <p class="page-subtitle">趣味双色球彩票插件</p>
    </div>

    <div class="dcb-nav" style="justify-content: center; width: 100%; box-sizing: border-box;">
        <a href="{{ route('dcb.index') }}" class="active">双色球</a>
        <a href="{{ route('dcb.my-tickets') }}">我的彩票</a>
        <a href="{{ route('dcb.history') }}">开奖历史</a>
        <a href="{{ route('dcb.verify') }}">验证公平性</a>
    </div>

    <div class="piggo-card" style="background: rgba(255,255,255,0.6); margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div>
                <strong style="font-size: 1.2em; color: var(--piggo-blue);">👋 {{ $user->username }}</strong>
            </div>
            <div class="balance" style="font-size: 1.2em; font-weight: bold; color: var(--piggo-orange);">
                余额: {{ number_format($user->seedbonus, 2) }} 
            </div>
        </div>
    </div>

    @if($currentPeriod)
    <div class="piggo-card" style="background: linear-gradient(135deg, var(--piggo-pink), var(--piggo-purple)); color: white; border: none;">
        <h2 style="margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">期号: {{ $currentPeriod->period_code }}</h2>
        <div style="font-size: 2em; font-weight: 900; margin: 10px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
            🏆 {{ number_format($currentPeriod->prize_pool, 2) }}
        </div>
        <p style="margin: 0; opacity: 0.9;">花费: {{ $config['price_per_ticket'] }} / Ticket</p>
    </div>

    <div class="ball-selector">
        <div class="ball-group">
            <h3 style="color: var(--dcb-red);"><span style="display:inline-block;" class="animate-wiggle">🔴</span> 请选择 {{ $config['game_rules']['red_ball_count'] }} 个红球 (1-{{ $config['game_rules']['red_ball_max'] }})</h3>
            <div class="balls" id="red-balls" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; padding: 20px;">
                @for($i = 1; $i <= $config['game_rules']['red_ball_max']; $i++)
                <div class="dcb-ball red" data-number="{{ $i }}" data-type="red">{{ $i }}</div>
                @endfor
            </div>
        </div>

        <div class="ball-group">
            <h3 style="color: var(--dcb-blue);"><span style="display:inline-block;" class="animate-wiggle">🔵</span> 请选择 {{ $config['game_rules']['blue_ball_count'] }} 个蓝球 (1-{{ $config['game_rules']['blue_ball_max'] }})</h3>
            <div class="balls" id="blue-balls" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; padding: 20px;">
                @for($i = 1; $i <= $config['game_rules']['blue_ball_max']; $i++)
                <div class="dcb-ball blue" data-number="{{ $i }}" data-type="blue">{{ $i }}</div>
                @endfor
            </div>
        </div>
    </div>

    <div class="piggo-card" style="text-align: center;">
        <h4 style="color: #666;">已选号码</h4>
        <div class="selected-list" style="font-size: 1.2em; margin: 15px 0;">
            <div style="margin-bottom: 5px;">🔴: <span id="selected-red" style="color: var(--dcb-red); font-weight: bold;">-</span></div>
            <div>🔵: <span id="selected-blue" style="color: var(--dcb-blue); font-weight: bold;">-</span></div>
        </div>
    </div>

    <div class="buttons" style="display: flex; gap: 20px; justify-content: center; margin: 40px 0;">
        <button class="piggo-btn btn-danger" onclick="clearSelection()">🧹 清空选择</button>
        <button class="piggo-btn btn-primary" onclick="quickPick()">🎲 机选一注</button>
        <button class="piggo-btn btn-success" onclick="buyTicket()">🛒 立即购买</button>
    </div>
    @else
    <div class="piggo-card" style="background: #ffebee; color: #c62828; text-align: center;">
        <h3>🚫 当前没有开放的期号</h3>
    </div>
    @endif

    @if($recentPeriods->count() > 0)
    <div class="recent-draws" style="margin-top: 50px;">
        <h3 style="text-align: center; color: var(--piggo-purple);">📜 开奖历史</h3>
        @foreach($recentPeriods as $period)
        @if($period->isDrawn())
        <div class="piggo-card" style="padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>第 {{ $period->period_code }} 期</strong>
                <div style="font-size: 0.8em; color: #999;">{{ $period->opened_at->format('Y-m-d H:i') }}</div>
            </div>
            <div style="display: flex; gap: 5px;">
                @foreach($period->red_balls as $ball)
                <div class="dcb-ball red" style="width: 30px; height: 30px; font-size: 0.9em;">{{ $ball }}</div>
                @endforeach
                @foreach($period->blue_balls as $ball)
                <div class="dcb-ball blue" style="width: 30px; height: 30px; font-size: 0.9em;">{{ $ball }}</div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const config = {
        redCount: {{ $config['game_rules']['red_ball_count'] }},
        blueCount: {{ $config['game_rules']['blue_ball_count'] }},
        redMax: {{ $config['game_rules']['red_ball_max'] }},
        blueMax: {{ $config['game_rules']['blue_ball_max'] }},
        periodId: {{ $currentPeriod ? $currentPeriod->id : 'null' }},
        csrfToken: '{{ csrf_token() }}'
    };

    let selectedRed = [];
    let selectedBlue = [];

    document.querySelectorAll('.dcb-ball').forEach(ball => {
        ball.addEventListener('click', function() {
            // Check if this is a display-only ball (like in history)
            if(!this.dataset.number) return; 

            const number = parseInt(this.dataset.number);
            const type = this.dataset.type;

            if (type === 'red') {
                toggleSelection(selectedRed, number, config.redCount, this);
            } else {
                toggleSelection(selectedBlue, number, config.blueCount, this);
            }

            updateDisplay();
        });
    });

    function toggleSelection(arr, number, maxCount, element) {
        const index = arr.indexOf(number);
        if (index > -1) {
            arr.splice(index, 1);
            element.classList.remove('selected');
        } else {
            if (arr.length < maxCount) {
                arr.push(number);
                element.classList.add('selected');
            } else {
                window.DCB.showToast('已达到最大选择数量', 'error');
            }
        }
    }

    function updateDisplay() {
        document.getElementById('selected-red').textContent = selectedRed.sort((a,b) => a-b).join(', ') || '-';
        document.getElementById('selected-blue').textContent = selectedBlue.sort((a,b) => a-b).join(', ') || '-';
    }

    function clearSelection() {
        selectedRed = [];
        selectedBlue = [];
        document.querySelectorAll('.dcb-ball').forEach(b => b.classList.remove('selected'));
        updateDisplay();
        window.DCB.showToast('选择已清空', 'info');
    }

    function quickPick() {
        // Clear UI first manually to avoid multiple "cleared" toasts
        selectedRed = [];
        selectedBlue = [];
        document.querySelectorAll('.dcb-ball').forEach(b => b.classList.remove('selected'));
        
        selectedRed = getRandomNumbers(1, config.redMax, config.redCount);
        selectedBlue = getRandomNumbers(1, config.blueMax, config.blueCount);

        document.querySelectorAll('.dcb-ball.red').forEach(ball => {
            if (selectedRed.includes(parseInt(ball.dataset.number))) {
                ball.classList.add('selected');
            }
        });

        document.querySelectorAll('.dcb-ball.blue').forEach(ball => {
            if (selectedBlue.includes(parseInt(ball.dataset.number))) {
                ball.classList.add('selected');
            }
        });

        updateDisplay();
        window.DCB.showToast('机选成功! 祝你好运! 🍀', 'success');
    }

    function getRandomNumbers(min, max, count) {
        const numbers = [];
        while (numbers.length < count) {
            const num = Math.floor(Math.random() * (max - min + 1)) + min;
            if (!numbers.includes(num)) {
                numbers.push(num);
            }
        }
        return numbers.sort((a,b) => a-b);
    }

    function buyTicket() {
        if (selectedRed.length !== config.redCount) {
            window.DCB.showToast('请选择 ' + config.redCount + ' 个红球', 'error');
            return;
        }

        if (selectedBlue.length !== config.blueCount) {
            window.DCB.showToast('请选择 ' + config.blueCount + ' 个蓝球', 'error');
            return;
        }

        fetch('{{ route('dcb.buy') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({
                period_id: config.periodId,
                red_balls: selectedRed,
                blue_balls: selectedBlue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.DCB.showToast(data.message, 'success');
                window.DCB.celebrate();
                // Don't clear immediately so user can see what they bought
                setTimeout(() => location.reload(), 2000);
            } else {
                window.DCB.showToast(data.message, 'error');
            }
        })
        .catch(error => {
            window.DCB.showToast('购买失败，请重试', 'error');
            console.error(error);
        });
    }
</script>
@endsection
