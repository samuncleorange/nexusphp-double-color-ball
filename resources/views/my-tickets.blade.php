@extends('dcb::layouts.app')

@section('title', nexus_trans('dcb::dcb.labels.my_tickets'))

@section('content')
<div class="piggo-card animate-float" style="max-width: 1000px; margin: 0 auto; margin-bottom: 30px;">
    <div class="page-header">
        <div style="font-size: 4em;">🎫</div>
        <h1 class="page-title">{{ nexus_trans('dcb::dcb.labels.my_tickets') }}</h1>
        <p class="page-subtitle">{{ $user->username }}</p>
    </div>

    <div class="dcb-nav" style="justify-content: center; width: 100%; box-sizing: border-box;">
        <a href="{{ route('dcb.index') }}">← {{ nexus_trans('dcb::dcb.buttons.buy_now') }}</a>
        <a href="{{ route('dcb.history') }}">{{ nexus_trans('dcb::dcb.buttons.view_history') }}</a>
        <a href="{{ route('dcb.verify') }}">{{ nexus_trans('dcb::dcb.buttons.verify_fairness') }}</a>
    </div>

    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="piggo-card" style="background: linear-gradient(135deg, var(--piggo-blue), var(--piggo-purple)); color: white; text-align: center; margin-bottom: 0;">
            <h3 style="margin: 0 0 10px 0; opacity: 0.9;">{{ nexus_trans('dcb::dcb.labels.ticket_count') }}</h3>
            <div style="font-size: 2em; font-weight: 900;">{{ $tickets->total() }}</div>
        </div>
        <div class="piggo-card" style="background: linear-gradient(135deg, var(--piggo-green), #a8e063); color: white; text-align: center; margin-bottom: 0;">
            <h3 style="margin: 0 0 10px 0; opacity: 0.9;">{{ nexus_trans('dcb::dcb.labels.total_winnings') }}</h3>
            <div style="font-size: 2em; font-weight: 900;">{{ number_format($totalWinnings, 0) }}</div>
        </div>
        <div class="piggo-card" style="background: linear-gradient(135deg, var(--piggo-orange), #ff9a9e); color: white; text-align: center; margin-bottom: 0;">
            <h3 style="margin: 0 0 10px 0; opacity: 0.9;">{{ nexus_trans('dcb::dcb.labels.total_spent') }}</h3>
            <div style="font-size: 2em; font-weight: 900;">{{ number_format($totalSpending, 0) }}</div>
        </div>
    </div>
</div>

@if($tickets->count() > 0)
    <div style="display: grid; gap: 20px;">
        @foreach($tickets as $ticket)
        <div class="piggo-card {{ $ticket->isWinner() ? 'winner-card' : '' }}" style="position: relative; overflow: hidden; margin-bottom: 0; {{ $ticket->isWinner() ? 'border: 2px solid var(--piggo-yellow); box-shadow: 0 0 15px rgba(255, 235, 59, 0.5);' : '' }}">
            @if($ticket->isWinner())
            <div style="position: absolute; top: 0; right: 0; background: var(--piggo-yellow); color: #d84315; padding: 5px 20px; font-weight: bold; transform: rotate(45deg) translate(25px, -15px); box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                WINNER
            </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 15px;">
                <div>
                    <span style="background: #333; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.9em;">
                        #{{ $ticket->id }}
                    </span>
                    <strong style="font-size: 1.2em; margin-left: 10px;">{{ nexus_trans('dcb::dcb.labels.period_code') }}: {{ $ticket->period->period_code }}</strong>
                </div>
                <div>
                    @if($ticket->period->isDrawn())
                        @if($ticket->isWinner())
                        <span style="color: var(--piggo-green); font-weight: bold; font-size: 1.2em;">
                            🎉 {{ $ticket->win_level_text }}
                        </span>
                        @else
                        <span style="color: #999; font-weight: bold;">
                           🍃 未中奖
                        </span>
                        @endif
                    @else
                        <span style="background: var(--piggo-blue); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                            ⏳ 等待开奖
                        </span>
                    @endif
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; align-items: center; justify-content: center; flex-wrap: wrap;">
                    @foreach($ticket->red_balls as $ball)
                    <div class="dcb-ball red" style="width: 40px; height: 40px; font-size: 1em;">{{ $ball }}</div>
                    @endforeach
                    <div style="width: 20px;"></div>
                    @foreach($ticket->blue_balls as $ball)
                    <div class="dcb-ball blue" style="width: 40px; height: 40px; font-size: 1em;">{{ $ball }}</div>
                    @endforeach
                </div>
            </div>

            @if($ticket->period->isDrawn())
            <div style="background: rgba(0,0,0,0.03); padding: 15px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
                <div style="color: #666; font-size: 0.9em;">
                    <strong>开奖号码:</strong>
                    @foreach($ticket->period->red_balls as $ball) <span style="color: var(--dcb-red); font-weight: bold;">{{ $ball }}</span> @endforeach
                    +
                    @foreach($ticket->period->blue_balls as $ball) <span style="color: var(--dcb-blue); font-weight: bold;">{{ $ball }}</span> @endforeach
                </div>
            </div>
            @endif

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd; display: flex; justify-content: space-between; color: #888; font-size: 0.9em;">
                <div>
                    🕒 {{ $ticket->created_at->format('Y-m-d H:i') }}
                </div>
                <div>
                    💰 {{ number_format($ticket->cost, 0) }} {{ nexus_trans('dcb::dcb.labels.magic_points') }}
                    @if($ticket->isWinner())
                    <span style="color: var(--piggo-green); font-weight: bold; font-size: 1.2em; margin-left: 10px;">
                        +{{ number_format($ticket->win_bonus, 0) }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 40px; display: flex; justify-content: center;">
        {{ $tickets->links('pagination::simple-bootstrap-4') }}
    </div>
@else
    <div class="piggo-card animate-float" style="text-align: center; padding: 50px;">
        <div style="font-size: 5em;">🐷❓</div>
        <h3 style="color: #666;">还没有购买记录</h3>
        <p style="color: #999; margin-bottom: 30px;">快去试试手气吧！</p>
        <a href="{{ route('dcb.index') }}" class="piggo-btn btn-primary" style="text-decoration: none;">立即购买</a>
    </div>
@endif

@endsection
