@extends('dcb::layouts.app')

@section('title', nexus_trans('dcb::dcb.labels.draw_history'))

@section('content')
<div class="piggo-card animate-float" style="max-width: 1000px; margin: 0 auto; margin-bottom: 30px;">
    <div class="page-header">
        <div style="font-size: 4em;">📜</div>
        <h1 class="page-title">{{ nexus_trans('dcb::dcb.labels.draw_history') }}</h1>
        <p class="page-subtitle">{{ nexus_trans('dcb::dcb.description') }}</p>
    </div>

    <div class="dcb-nav" style="justify-content: center; width: 100%; box-sizing: border-box;">
        <a href="{{ route('dcb.index') }}">← {{ nexus_trans('dcb::dcb.buttons.buy_now') }}</a>
        <a href="{{ route('dcb.my-tickets') }}">{{ nexus_trans('dcb::dcb.buttons.view_my_tickets') }}</a>
        <a href="{{ route('dcb.verify') }}">{{ nexus_trans('dcb::dcb.buttons.verify_fairness') }}</a>
    </div>
</div>

@if($periods->count() > 0)
    <div style="display: grid; gap: 30px; max-width: 1000px; margin: 0 auto;">
        @foreach($periods as $period)
        <div class="piggo-card" style="margin-bottom: 0; border: 2px solid rgba(255,255,255,0.8);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; border-bottom: 2px dashed #eee; padding-bottom: 20px; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0; color: var(--piggo-blue);">第 {{ $period->period_code }} 期</h2>
                    <div style="color: #999; margin-top: 5px;">
                        📅 {{ $period->opened_at->format('Y年m月d日 H:i') }}
                    </div>
                </div>
                
                <div class="piggo-card" style="background: linear-gradient(135deg, #ffd700, #ffecb3); color: #856404; padding: 10px 20px; border-radius: 15px; margin: 0; box-shadow: none;">
                    <div style="font-size: 0.9em; text-transform: uppercase; font-weight: bold;">{{ nexus_trans('dcb::dcb.labels.prize_pool') }}</div>
                    <div style="font-size: 1.5em; font-weight: 900;">{{ number_format($period->prize_pool, 0) }}</div>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h3 style="text-align: center; color: #666; margin-bottom: 20px;">🎯 {{ nexus_trans('dcb::dcb.labels.winning_numbers') }}</h3>
                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                    @foreach($period->red_balls as $ball)
                    <div class="dcb-ball red">{{ $ball }}</div>
                    @endforeach
                    <div class="dcb-ball blue">{{ $period->blue_balls[0] ?? '?' }}</div>
                </div>
            </div>

            @if($period->win_details)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
                @foreach($period->win_details as $level => $details)
                    @if($level !== 'rollover' && isset($details['count']) && $details['count'] > 0)
                    <div style="background: #f8f9fa; border-radius: 10px; padding: 15px; text-align: center; border: 1px solid #eee;">
                        <div style="color: #666; font-size: 0.9em; margin-bottom: 5px;">{{ nexus_trans("dcb::dcb.win_level.level_{$level}") }}</div>
                        <div style="font-size: 1.2em; font-weight: bold; color: var(--piggo-blue);">{{ $details['count'] }} 注</div>
                        <div style="color: var(--piggo-green); font-size: 0.85em;">
                            💰 {{ number_format($details['per_winner'], 0) }}
                        </div>
                    </div>
                    @endif
                @endforeach
                
                @if(isset($period->win_details['rollover']) && $period->win_details['rollover'] > 0)
                <div style="background: #fff3cd; border-radius: 10px; padding: 15px; text-align: center; border: 1px solid #ffeeba;">
                    <div style="color: #856404; font-size: 0.9em; margin-bottom: 5px;">下期滚存</div>
                    <div style="font-size: 1.2em; font-weight: bold; color: #856404;">
                        📈 {{ number_format($period->win_details['rollover'], 0) }}
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div style="background: rgba(33, 150, 243, 0.1); padding: 15px; border-radius: 10px; font-size: 0.9em; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="color: var(--piggo-blue);">
                    <strong>🔒 {{ nexus_trans('dcb::dcb.labels.fairness_verification') }}:</strong> 
                    区块 #{{ $period->block_height }}
                    <code style="background: rgba(255,255,255,0.5); padding: 2px 5px; border-radius: 4px; margin: 0 5px;">{{ substr($period->block_hash, 0, 10) }}...{{ substr($period->block_hash, -10) }}</code>
                </div>
                <a href="{{ route('dcb.verify') }}" class="piggo-btn btn-primary" style="padding: 5px 15px; font-size: 0.8em; text-decoration: none;">🔍 {{ nexus_trans('dcb::dcb.buttons.verify_fairness') }}</a>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 40px; display: flex; justify-content: center;">
        {{ $periods->links('pagination::simple-bootstrap-4') }}
    </div>
@else
    <div class="piggo-card animate-float" style="text-align: center; padding: 50px; max-width: 800px; margin: 0 auto;">
        <div style="font-size: 5em;">⏳</div>
        <h3 style="color: #666;">暂无历史记录</h3>
        <p style="color: #999;">第一期开奖后将在这里显示。</p>
    </div>
@endif

@endsection
