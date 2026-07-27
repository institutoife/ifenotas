<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ife notas - live</title>
    <link rel="icon" href="{{ asset('images/ife.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        :root{
            --primary:#26baa5;
            --secondary:#375f7a;
            --bg:#f5fbfb;
            --card:#ffffff;
            --line:#d7e7e5;
            --muted:#5d7482;
            --ok:#16a34a;
            --warn:#f59e0b;
            --danger:#ef4444;
            --state:#f59e0b;
            --state-soft:rgba(245,158,11,.14);
            --state-shadow:rgba(245,158,11,.36);
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:"Segoe UI",Arial,sans-serif;
            background:radial-gradient(circle at top left, rgba(38,186,165,.16), transparent 34%), var(--bg);
            color:var(--secondary);
        }
        .live-shell{
            width:min(1180px,100%);
            min-height:100vh;
            margin:0 auto;
            padding:4px clamp(8px,1.4vw,16px) clamp(8px,1.4vw,16px);
            display:grid;
            grid-template-rows:auto auto auto auto;
            align-content:start;
            gap:clamp(4px,.7vw,8px);
        }
        .live-header{
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            gap:3px;
            min-height:auto;
        }
        .live-logo{
            display:block;
            width:min(360px,70vw);
            max-height:110px;
            object-fit:contain;
        }
        .live-marquee{
            display:block;
            width:min(1080px,96vw);
            overflow:hidden;
            border:1px solid rgba(38,186,165,.38);
            border-radius:12px;
            background:linear-gradient(90deg,#ffffff 0%,rgba(38,186,165,.13) 50%,#ffffff 100%);
            color:var(--secondary);
            text-decoration:none;
            box-shadow:0 8px 22px rgba(55,95,122,.10);
        }
        .live-marquee:hover{
            border-color:var(--primary);
            box-shadow:0 10px 24px rgba(38,186,165,.16);
        }
        .live-marquee-track{
            display:flex;
            width:max-content;
            animation:liveMarquee 18s linear infinite;
        }
        .live-marquee:hover .live-marquee-track{animation-play-state:paused}
        .live-marquee-message{
            display:inline-flex;
            align-items:center;
            white-space:nowrap;
            padding:clamp(8px,1vw,12px) clamp(30px,4.5vw,58px);
            font-size:clamp(2rem,5vw,4rem);
            line-height:1;
            font-weight:1000;
            letter-spacing:0;
            text-transform:uppercase;
        }
        .live-marquee-message::before{
            content:"";
            width:.62em;
            height:.62em;
            margin-right:.55em;
            border-radius:50%;
            background:var(--primary);
            box-shadow:0 0 0 .22em rgba(38,186,165,.15);
            flex:0 0 auto;
        }
        @keyframes liveMarquee{
            from{transform:translateX(0)}
            to{transform:translateX(-50%)}
        }
        .note-grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:clamp(10px,1.6vw,18px);
        }
        .note-card{
            position:relative;
            overflow:hidden;
            min-height:clamp(158px,22vh,230px);
            border:4px solid var(--line);
            border-radius:22px;
            background:var(--card);
            box-shadow:0 18px 40px rgba(55,95,122,.10);
            padding:clamp(12px,1.8vw,20px);
            display:grid;
            grid-template-rows:auto 1fr;
            gap:8px;
            transition:border-color .25s ease, box-shadow .25s ease, transform .25s ease;
        }
        .note-card.changed{animation:cardPop .6s ease}
        .note-card::after{
            content:"";
            position:absolute;
            inset:auto 0 0;
            height:10px;
            background:var(--state);
            opacity:.86;
            transform:scaleX(.75);
            transform-origin:left;
            transition:background .25s ease, transform .35s ease;
        }
        .note-card.filled::after{transform:scaleX(1)}
        .note-card.ok{border-color:rgba(22,163,74,.72);box-shadow:0 20px 44px rgba(22,163,74,.16)}
        .note-card.warning{border-color:rgba(245,158,11,.82);box-shadow:0 20px 44px rgba(245,158,11,.18)}
        .note-card.risk{border-color:rgba(239,68,68,.78);box-shadow:0 20px 44px rgba(239,68,68,.16)}
        .note-label{
            font-size:clamp(1rem,2.2vw,1.55rem);
            font-weight:950;
            color:var(--muted);
            text-transform:uppercase;
            text-align:center;
        }
        .note-input{
            width:100%;
            min-width:0;
            border:0;
            background:transparent;
            color:var(--state);
            font-size:clamp(4.2rem,15vw,10.8rem);
            line-height:.86;
            font-weight:1000;
            text-align:center;
            outline:none;
            padding:0;
            caret-color:var(--secondary);
            transition:color .25s ease, transform .25s ease;
        }
        .note-input::placeholder{color:rgba(55,95,122,.23)}
        .note-input:focus{transform:scale(1.03)}
        .note-value{
            width:100%;
            min-width:0;
            color:var(--state);
            font-size:clamp(4.2rem,15vw,10.8rem);
            line-height:.86;
            font-weight:1000;
            text-align:center;
            transition:color .25s ease, transform .25s ease;
        }
        .note-value.changed{animation:cardPop .6s ease}
        .over-label{
            position:absolute;
            top:clamp(42px,5vw,58px);
            right:clamp(10px,1.4vw,18px);
            display:none;
            border-radius:999px;
            background:var(--danger);
            color:#fff;
            padding:6px 10px;
            font-size:clamp(.82rem,1.6vw,1.15rem);
            font-weight:1000;
            line-height:1;
            box-shadow:0 10px 22px rgba(239,68,68,.28);
        }
        .over-label.show{display:inline-flex}
        .progress-zone{
            display:grid;
            gap:clamp(2px,.45vw,5px);
            align-content:center;
            padding:2px 0;
        }
        .total-score{
            text-align:center;
            color:var(--secondary);
            font-size:clamp(1.45rem,3.8vw,3.6rem);
            line-height:1;
            font-weight:1000;
            text-transform:uppercase;
        }
        .total-score strong{
            color:var(--state);
            font-size:clamp(3rem,9vw,7.5rem);
            line-height:.86;
            transition:color .25s ease;
        }
        .status-title{
            text-align:center;
            font-size:clamp(3.1rem,9.5vw,8.8rem);
            line-height:.9;
            font-weight:1000;
            color:var(--state);
            text-transform:uppercase;
            transition:color .25s ease;
        }
        .progress-wrap{
            position:relative;
            padding:clamp(10px,1.8vw,20px) 0 clamp(6px,1.2vw,14px);
        }
        .progress-track{
            position:relative;
            height:clamp(36px,6.5vw,72px);
            border-radius:999px;
            background:var(--state);
            box-shadow:inset 0 0 0 6px rgba(255,255,255,.72), 0 20px 48px var(--state-shadow);
            overflow:hidden;
            transition:background .25s ease, box-shadow .25s ease;
        }
        .progress-fill{
            width:0%;
            height:100%;
            background:rgba(255,255,255,.28);
            border-right:8px solid rgba(255,255,255,.85);
            transition:width .55s cubic-bezier(.2,.9,.2,1);
        }
        .progress-marker{
            position:absolute;
            top:50%;
            left:0%;
            width:clamp(54px,8vw,94px);
            height:clamp(54px,8vw,94px);
            border:8px solid #fff;
            border-radius:50%;
            background:var(--state);
            box-shadow:0 16px 34px var(--state-shadow), 0 0 0 9px rgba(255,255,255,.42);
            transform:translate(-50%,-50%);
            transition:left .55s cubic-bezier(.2,.9,.2,1), background .25s ease, box-shadow .25s ease;
            pointer-events:none;
        }
        .progress-marker.changed{animation:markerPulse .7s ease}
        .range-control{
            position:absolute;
            inset:0 0 auto;
            top:clamp(10px,1.8vw,20px);
            width:100%;
            height:clamp(36px,6.5vw,72px);
            margin:0;
            padding:0;
            border:0;
            opacity:.001;
            cursor:pointer;
        }
        .progress-labels{
            display:flex;
            justify-content:space-between;
            gap:10px;
            margin-top:4px;
            color:var(--muted);
            font-size:clamp(.9rem,1.8vw,1.25rem);
            font-weight:900;
        }
        .summary{
            border:4px solid var(--state);
            border-radius:24px;
            background:linear-gradient(135deg,var(--state-soft),#fff 68%);
            box-shadow:0 20px 44px var(--state-shadow);
            padding:clamp(8px,1.3vw,16px);
            display:grid;
            grid-template-columns:1.1fr .9fr;
            gap:clamp(12px,2vw,22px);
            align-items:center;
            transition:border-color .25s ease, box-shadow .25s ease, background .25s ease;
        }
        .missing{
            text-align:center;
        }
        .missing span,.target-box span{
            display:block;
            color:var(--muted);
            font-size:clamp(.92rem,1.9vw,1.25rem);
            font-weight:950;
            text-transform:uppercase;
        }
        .missing strong{
            display:block;
            color:var(--state);
            font-size:clamp(4rem,12vw,9rem);
            line-height:.9;
            font-weight:1000;
            transition:color .25s ease;
        }
        .target-grid{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:12px;
        }
        .target-box{
            min-height:130px;
            border:3px solid rgba(55,95,122,.13);
            border-radius:18px;
            background:rgba(255,255,255,.78);
            padding:14px;
            text-align:center;
            display:grid;
            align-content:center;
        }
        .target-box strong{
            color:var(--secondary);
            font-size:clamp(3rem,8vw,6.5rem);
            line-height:.9;
            font-weight:1000;
        }
        .helper-text{
            grid-column:1 / -1;
            text-align:center;
            color:var(--secondary);
            font-size:clamp(1.05rem,2.2vw,1.7rem);
            font-weight:950;
        }
        .prize-open{
            position:fixed;
            right:18px;
            bottom:18px;
            z-index:30;
            border:0;
            border-radius:999px;
            background:linear-gradient(135deg,#26baa5,#375f7a);
            color:#fff;
            padding:14px 20px;
            font-size:clamp(.95rem,1.6vw,1.2rem);
            font-weight:1000;
            text-transform:uppercase;
            cursor:pointer;
            box-shadow:0 16px 34px rgba(55,95,122,.28);
            transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }
        .prize-open:hover{
            transform:translateY(-2px) scale(1.04);
            filter:saturate(1.12);
            box-shadow:0 20px 42px rgba(55,95,122,.34);
        }
        .prize-open:active{transform:translateY(1px) scale(1.08)}
        .prize-modal{
            position:fixed;
            inset:0;
            z-index:40;
            display:grid;
            place-items:center;
            padding:18px;
            background:rgba(18,43,60,.58);
            opacity:0;
            pointer-events:none;
            transition:opacity .28s ease;
        }
        .prize-modal.open{
            opacity:1;
            pointer-events:auto;
        }
        .prize-card{
            position:relative;
            width:min(720px,100%);
            max-height:min(92vh,900px);
            overflow-y:auto;
            overflow-x:hidden;
            border:5px solid rgba(38,186,165,.9);
            border-radius:24px;
            background:linear-gradient(145deg,#fff 0%,#ecfffb 46%,#fff7dc 100%);
            box-shadow:0 28px 70px rgba(18,43,60,.35);
            padding:clamp(18px,3vw,32px);
            text-align:center;
            transform:translateY(28px) scale(.94);
        }
        .prize-modal.open .prize-card{animation:prizeEnter .55s cubic-bezier(.18,.9,.22,1.15) forwards}
        .prize-card::before,
        .prize-card::after{
            content:"";
            position:absolute;
            width:230px;
            height:230px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(38,186,165,.28),transparent 68%);
            animation:prizeGlow 3.2s ease-in-out infinite alternate;
        }
        .prize-card::before{top:-95px;left:-85px}
        .prize-card::after{right:-100px;bottom:-105px;background:radial-gradient(circle,rgba(245,158,11,.32),transparent 68%);animation-delay:.5s}
        .prize-close{
            position:absolute;
            top:12px;
            right:12px;
            z-index:2;
            width:42px;
            height:42px;
            border:0;
            border-radius:50%;
            background:rgba(55,95,122,.12);
            color:var(--secondary);
            font-size:1.5rem;
            line-height:1;
            font-weight:1000;
            cursor:pointer;
        }
        .prize-close:hover{background:rgba(55,95,122,.2)}
        .prize-kicker{
            position:relative;
            z-index:1;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:999px;
            background:var(--primary);
            color:#fff;
            padding:8px 16px;
            font-size:clamp(.9rem,1.8vw,1.15rem);
            font-weight:1000;
            text-transform:uppercase;
            box-shadow:0 10px 24px rgba(38,186,165,.26);
        }
        .prize-title{
            position:relative;
            z-index:1;
            margin:14px 0 8px;
            color:var(--secondary);
            font-size:clamp(2rem,5.4vw,4.2rem);
            line-height:.95;
            font-weight:1000;
            text-transform:uppercase;
        }
        .prize-help{
            position:relative;
            z-index:1;
            max-width:520px;
            margin:0 auto 16px;
            color:var(--muted);
            font-size:clamp(1rem,2vw,1.25rem);
            font-weight:850;
            line-height:1.2;
        }
        .prize-number-wrap{
            position:relative;
            z-index:1;
            display:grid;
            place-items:center;
            width:min(290px,72vw);
            aspect-ratio:1;
            margin:0 auto 18px;
            border:8px solid #fff;
            border-radius:50%;
            background:conic-gradient(from 0deg,#26baa5,#f59e0b,#ef4444,#375f7a,#26baa5);
            box-shadow:0 18px 42px rgba(55,95,122,.26);
            animation:prizeDial 5s linear infinite;
        }
        .prize-number{
            display:grid;
            place-items:center;
            width:78%;
            aspect-ratio:1;
            border-radius:50%;
            background:#fff;
            color:#ef4444;
            font-size:clamp(5.2rem,18vw,9.5rem);
            line-height:1;
            font-weight:1000;
            box-shadow:inset 0 0 0 8px rgba(38,186,165,.12);
            transition:transform .12s ease, color .12s ease;
        }
        .prize-number.spinning{
            color:#f59e0b;
            animation:numberShake .16s linear infinite;
        }
        .prize-number.done{
            color:#16a34a;
            animation:numberWin .75s ease;
        }
        .prize-actions{
            position:relative;
            z-index:1;
            display:flex;
            justify-content:center;
            gap:10px;
            flex-wrap:wrap;
            margin-bottom:14px;
        }
        .prize-spin{
            border:0;
            border-radius:999px;
            background:linear-gradient(135deg,#ef4444,#f59e0b);
            color:#fff;
            padding:15px 24px;
            font-size:clamp(1rem,2.2vw,1.35rem);
            font-weight:1000;
            text-transform:uppercase;
            cursor:pointer;
            box-shadow:0 16px 32px rgba(239,68,68,.32);
            transform:translateY(0) scale(1);
            transition:transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }
        .prize-spin:hover{transform:translateY(-2px) scale(1.03);filter:saturate(1.14)}
        .prize-spin:active,.prize-spin.spinning{
            transform:translateY(2px) scale(1.08);
            box-shadow:0 0 0 10px rgba(245,158,11,.22),0 22px 42px rgba(239,68,68,.38);
            animation:buttonBlast .36s ease-in-out infinite alternate;
        }
        .prize-spin:disabled{cursor:wait}
        .winner-register{
            position:relative;
            z-index:1;
            display:grid;
            gap:10px;
            margin-top:6px;
            padding:12px;
            border:2px solid rgba(55,95,122,.12);
            border-radius:16px;
            background:rgba(255,255,255,.72);
        }
        .winner-register-title{
            margin:0;
            color:var(--secondary);
            font-size:clamp(1.05rem,2vw,1.35rem);
            line-height:1.05;
            font-weight:1000;
            text-transform:uppercase;
        }
        .winner-form{
            display:grid;
            grid-template-columns:1fr 1fr auto;
            gap:8px;
        }
        .winner-input{
            width:100%;
            min-width:0;
            border:2px solid rgba(55,95,122,.16);
            border-radius:10px;
            background:#fff;
            color:var(--secondary);
            padding:11px 12px;
            font:inherit;
            font-size:1rem;
            font-weight:800;
            outline:none;
        }
        .winner-input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(38,186,165,.14);
        }
        .winner-form .select2-container{width:100%!important}
        .winner-form .select2-container .select2-selection--single{
            height:48px;
            border:2px solid rgba(55,95,122,.16);
            border-radius:10px;
            background:#fff;
            outline:none;
        }
        .winner-form .select2-container--default.select2-container--focus .select2-selection--single,
        .winner-form .select2-container--default.select2-container--open .select2-selection--single{
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(38,186,165,.14);
        }
        .winner-form .select2-container .select2-selection__rendered{
            color:var(--secondary);
            padding-left:12px;
            padding-right:34px;
            line-height:44px;
            font-size:1rem;
            font-weight:800;
            text-align:left;
        }
        .winner-form .select2-container .select2-selection__arrow{
            height:44px;
            right:8px;
        }
        .select2-dropdown{
            border:2px solid rgba(55,95,122,.16);
            border-radius:10px;
            overflow:hidden;
            box-shadow:0 16px 34px rgba(55,95,122,.18);
            z-index:60;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field{
            border:2px solid rgba(38,186,165,.22);
            border-radius:8px;
            padding:8px 10px;
            color:var(--secondary);
            font:inherit;
            font-weight:800;
            outline:none;
        }
        .select2-results__option{
            color:var(--secondary);
            font-weight:850;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
            background:var(--primary);
        }
        .winner-save{
            border:0;
            border-radius:10px;
            background:var(--secondary);
            color:#fff;
            padding:0 16px;
            font-size:.95rem;
            font-weight:1000;
            text-transform:uppercase;
            cursor:pointer;
            box-shadow:0 10px 22px rgba(55,95,122,.2);
        }
        .winner-save:hover{filter:brightness(1.08)}
        .winner-list{
            display:grid;
            gap:7px;
            max-height:150px;
            overflow:auto;
            padding-right:2px;
        }
        .winner-empty{
            margin:0;
            color:var(--muted);
            font-size:.95rem;
            font-weight:850;
        }
        .winner-item{
            display:grid;
            grid-template-columns:auto 1fr;
            gap:8px 10px;
            align-items:center;
            border-radius:12px;
            background:#fff;
            padding:9px 10px;
            text-align:left;
            box-shadow:0 8px 18px rgba(55,95,122,.08);
        }
        .winner-number{
            display:grid;
            place-items:center;
            width:42px;
            height:42px;
            border-radius:50%;
            background:rgba(38,186,165,.14);
            color:var(--primary);
            font-size:1.15rem;
            font-weight:1000;
        }
        .winner-detail strong{
            display:block;
            color:var(--secondary);
            font-size:1rem;
            line-height:1.1;
            font-weight:1000;
        }
        .winner-detail span{
            display:block;
            color:var(--muted);
            font-size:.9rem;
            line-height:1.15;
            font-weight:850;
        }
        @keyframes prizeEnter{
            0%{transform:translateY(28px) scale(.94);opacity:.2}
            60%{transform:translateY(-8px) scale(1.03);opacity:1}
            100%{transform:translateY(0) scale(1);opacity:1}
        }
        @keyframes prizeGlow{
            from{transform:scale(.9);opacity:.6}
            to{transform:scale(1.18);opacity:1}
        }
        @keyframes prizeDial{
            to{filter:hue-rotate(360deg)}
        }
        @keyframes numberShake{
            0%,100%{transform:translateX(0) scale(1)}
            25%{transform:translateX(-4px) scale(1.04)}
            75%{transform:translateX(4px) scale(1.04)}
        }
        @keyframes numberWin{
            0%{transform:scale(.9)}
            42%{transform:scale(1.22)}
            100%{transform:scale(1)}
        }
        @keyframes buttonBlast{
            from{filter:brightness(1)}
            to{filter:brightness(1.18) saturate(1.25)}
        }
        .flash .status-title,.flash .summary,.flash .note-input{animation:flashIn .55s ease}
        .flash .total-score{animation:flashIn .55s ease}
        @keyframes cardPop{
            0%{transform:scale(1)}
            42%{transform:scale(1.035)}
            100%{transform:scale(1)}
        }
        @keyframes markerPulse{
            0%{transform:translate(-50%,-50%) scale(1)}
            45%{transform:translate(-50%,-50%) scale(1.24)}
            100%{transform:translate(-50%,-50%) scale(1)}
        }
        @keyframes flashIn{
            0%{filter:brightness(1)}
            36%{filter:brightness(1.18)}
            100%{filter:brightness(1)}
        }
        @media(max-width:780px){
            body{
                min-height:100svh;
                overflow-x:hidden;
            }
            .live-shell{
                width:100%;
                min-height:100svh;
                padding:8px 10px 76px;
                gap:10px;
            }
            .live-header{
                min-height:auto;
                gap:6px;
            }
            .live-logo{
                width:min(168px,48vw);
                max-height:50px;
            }
            .live-marquee{
                width:100%;
                border-radius:10px;
                box-shadow:0 5px 14px rgba(55,95,122,.08);
            }
            .live-marquee-message{
                font-size:clamp(.9rem,3.7vw,1.1rem);
                padding:7px 20px;
            }
            .note-grid{
                grid-template-columns:repeat(2,minmax(0,1fr));
                gap:8px;
            }
            .note-grid .note-card:first-child{
                grid-column:1 / -1;
                min-height:128px;
            }
            .note-card{
                min-height:108px;
                border-width:3px;
                border-radius:16px;
                padding:10px 8px;
                gap:5px;
                box-shadow:0 8px 20px rgba(55,95,122,.08);
            }
            .note-card::after{height:6px}
            .note-label{
                font-size:clamp(.7rem,3vw,.88rem);
                line-height:1.08;
            }
            .note-input,.note-value{
                font-size:clamp(3.25rem,17vw,4.6rem);
                line-height:.86;
            }
            .over-label{
                top:40px;
                right:7px;
                padding:4px 7px;
                font-size:.68rem;
            }
            .progress-zone{
                gap:5px;
                padding:4px 2px;
            }
            .total-score{
                font-size:clamp(.9rem,3.8vw,1.15rem);
            }
            .total-score strong{
                font-size:clamp(2.2rem,11vw,3.1rem);
            }
            .status-title{
                font-size:clamp(2rem,11vw,3.25rem);
                line-height:.9;
            }
            .progress-wrap{
                padding:12px 0 6px;
            }
            .progress-track{
                height:28px;
                box-shadow:inset 0 0 0 3px rgba(255,255,255,.72), 0 9px 22px var(--state-shadow);
            }
            .progress-marker{
                width:40px;
                height:40px;
                border-width:5px;
                box-shadow:0 6px 14px var(--state-shadow), 0 0 0 5px rgba(255,255,255,.42);
            }
            .range-control{
                top:4px;
                height:48px;
            }
            .progress-labels{
                margin-top:3px;
                font-size:.72rem;
                gap:6px;
            }
            .summary{
                grid-template-columns:1fr;
                gap:10px;
                border-width:3px;
                border-radius:18px;
                padding:14px 10px;
                box-shadow:0 10px 24px var(--state-shadow);
            }
            .missing span,.target-box span{
                font-size:clamp(.72rem,3vw,.88rem);
                line-height:1.1;
            }
            .missing{
                display:grid;
                align-content:center;
            }
            .missing strong{
                font-size:clamp(3.7rem,20vw,5.4rem);
                line-height:.9;
            }
            .target-grid{
                gap:8px;
            }
            .target-box{
                min-height:82px;
                border-width:2px;
                border-radius:12px;
                padding:9px 5px;
            }
            .target-box strong{
                font-size:clamp(2.3rem,12vw,3.25rem);
                line-height:.9;
            }
            .helper-text{
                font-size:clamp(.82rem,3.4vw,1rem);
                line-height:1.25;
            }
            .prize-modal{
                align-items:start;
                padding:8px;
                overflow:auto;
            }
            .prize-card{
                width:100%;
                max-height:calc(100svh - 16px);
                border-width:3px;
                border-radius:16px;
                padding:12px 10px;
                margin-top:0;
            }
            .prize-card::before,.prize-card::after{
                width:150px;
                height:150px;
            }
            .prize-close{
                top:8px;
                right:8px;
                width:34px;
                height:34px;
                font-size:1.25rem;
            }
            .prize-kicker{
                padding:5px 10px;
                font-size:.72rem;
            }
            .prize-title{
                margin:9px 36px 5px;
                font-size:clamp(1.45rem,7.6vw,2.2rem);
            }
            .prize-help{
                margin-bottom:9px;
                font-size:clamp(.78rem,3.2vw,.92rem);
            }
            .prize-number-wrap{
                width:min(156px,48vw);
                border-width:5px;
                margin-bottom:10px;
            }
            .prize-number{
                font-size:clamp(3.4rem,18vw,5rem);
                box-shadow:inset 0 0 0 5px rgba(38,186,165,.12);
            }
            .prize-actions{
                margin-bottom:9px;
            }
            .prize-spin{
                padding:11px 16px;
                font-size:.9rem;
            }
            .winner-register{
                gap:8px;
                padding:8px;
                border-radius:12px;
            }
            .winner-register-title{
                font-size:.95rem;
            }
            .winner-form{grid-template-columns:1fr}
            .winner-input{
                padding:9px 10px;
                font-size:.92rem;
            }
            .winner-form .select2-container .select2-selection--single{
                height:42px;
            }
            .winner-form .select2-container .select2-selection__rendered{
                line-height:38px;
                font-size:.92rem;
            }
            .winner-form .select2-container .select2-selection__arrow{
                height:38px;
            }
            .winner-save{min-height:44px}
            .winner-list{
                max-height:110px;
            }
            .winner-item{
                padding:7px 8px;
                gap:6px 8px;
            }
            .winner-number{
                width:34px;
                height:34px;
                font-size:.9rem;
            }
            .winner-detail strong{font-size:.9rem}
            .winner-detail span{font-size:.78rem}
            .prize-open{
                right:10px;
                bottom:max(10px,env(safe-area-inset-bottom));
                min-height:44px;
                padding:10px 14px;
                font-size:.72rem;
                box-shadow:0 10px 20px rgba(55,95,122,.25);
            }
        }
        @media(max-width:380px){
            .live-shell{padding-left:7px;padding-right:7px}
            .note-grid{gap:6px}
            .note-card{min-height:100px;padding:8px 5px}
            .note-label{font-size:.66rem}
            .note-input,.note-value{font-size:clamp(2.8rem,16vw,3.7rem)}
            .status-title{font-size:clamp(1.8rem,10vw,2.65rem)}
            .target-box strong{font-size:clamp(2rem,11vw,2.8rem)}
            .prize-number-wrap{width:min(140px,46vw)}
        }
        @media(max-width:780px) and (orientation:landscape){
            .live-shell{padding-bottom:66px}
            .note-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
            .note-grid .note-card:first-child{grid-column:auto;min-height:104px}
            .note-card{min-height:104px}
            .summary{grid-template-columns:.75fr 1.25fr}
        }
        @media(max-height:720px) and (min-width:781px){
            .live-shell{gap:4px;padding-top:4px}
            .live-header{min-height:auto}
            .live-logo{max-height:64px}
            .live-marquee-message{padding:7px 34px;font-size:clamp(1.7rem,3.7vw,2.8rem)}
            .note-card{min-height:142px}
            .summary{padding:14px}
            .target-box{min-height:96px}
        }
    </style>
</head>
<body>
    <main class="live-shell" id="liveShell">
        <header class="live-header">
            <img class="live-logo" src="{{ asset('images/ife.png') }}" alt="ife notas">
            <a class="live-marquee" href="https://notas.ife.bo" target="_blank" rel="noopener" aria-label="Abrir notas.ife.bo">
                <div class="live-marquee-track">
                    <span class="live-marquee-message">Comenta tu nota del primer trimestre y Sígueme. App: notas.ife.bo</span>
                    <span class="live-marquee-message">Comenta tu nota del primer trimestre y Sígueme. App: notas.ife.bo</span>
                </div>
            </a>
        </header>

        <section class="note-grid" aria-label="Notas trimestrales">
            <label class="note-card" id="cardFirst">
                <span class="note-label">Nota primer trimestre</span>
                <input class="note-input" id="firstTerm" type="number" min="0" max="100" inputmode="numeric" value="0">
            </label>
            <div class="note-card" id="cardSecond">
                <span class="note-label">Nota segundo trimestre</span>
                <div class="note-value" id="secondTerm">77</div>
            </div>
            <div class="note-card" id="cardThird">
                <span class="note-label">Nota tercer trimestre necesaria</span>
                <div class="note-value" id="thirdTerm">76</div>
                <span class="over-label" id="thirdOverLabel">más de 100</span>
            </div>
        </section>

        <section class="progress-zone" aria-live="polite">
            <div class="total-score">Suma total <strong id="totalScore">153</strong></div>
            <div class="status-title" id="statusTitle">Ingresa la nota</div>
            <div class="progress-wrap">
                <div class="progress-track">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-marker" id="progressMarker"></div>
                <input class="range-control" id="secondRange" type="range" min="0" max="100" value="77" aria-label="Nota del segundo trimestre">
                <div class="progress-labels">
                    <span>0</span>
                    <span>Segundo trimestre</span>
                    <span>100</span>
                </div>
            </div>
        </section>

        <section class="summary" id="summaryPanel">
            <div class="missing">
                <span>Puntos que faltan desde el primer trimestre</span>
                <strong id="missingPoints">{{ $passScore }}</strong>
            </div>
            <div class="target-grid">
                <div class="target-box">
                    <span>Escenario segundo</span>
                    <strong id="targetSecond">77</strong>
                </div>
                <div class="target-box">
                    <span>Debe sacar en tercero</span>
                    <strong id="targetThird">76</strong>
                </div>
                <div class="helper-text" id="helperText">Con primer trimestre 0, segundo 77 y tercero 76 suma 153.</div>
            </div>
        </section>
    </main>

    <button class="prize-open" id="prizeOpen" type="button">Abrir sorteo</button>

    <div class="prize-modal" id="prizeModal" hidden role="dialog" aria-modal="true" aria-labelledby="prizeTitle">
        <div class="prize-card">
            <button class="prize-close" id="prizeClose" type="button" aria-label="Cerrar sorteo">&times;</button>
            <div class="prize-kicker">Premio live</div>
            <h2 class="prize-title" id="prizeTitle">Sorteo de premio</h2>
            <p class="prize-help">El numero corresponde al orden de seguidores: el ultimo que siguio es 1, el penultimo es 2 y asi sucesivamente.</p>
            <div class="prize-number-wrap" aria-live="polite">
                <div class="prize-number" id="prizeNumber">?</div>
            </div>
            <div class="prize-actions">
                <button class="prize-spin" id="prizeSpin" type="button">Girar ruleta</button>
            </div>
            <section class="winner-register" aria-label="Registro de ganadores">
                <h3 class="winner-register-title">Ganadores del live</h3>
                <form class="winner-form" id="winnerForm" autocomplete="off">
                    <input class="winner-input" id="winnerName" type="text" placeholder="Nombre del ganador" aria-label="Nombre del ganador" maxlength="60" required>
                    <select class="winner-input winner-prize-select" id="winnerPrize" aria-label="Premio ganado" required>
                        <option value="Apoyo escolar">Apoyo escolar</option>
                        <option value="Nivel inicial">Nivel inicial</option>
                        <option value="Primaria">Primaria</option>
                        <option value="Secundaria">Secundaria</option>
                        <option value="Preuniversitario">Preuniversitario</option>
                        <option value="Apoyo universitario">Apoyo universitario</option>
                        <option value="Lectura y escritura">Lectura y escritura</option>
                        <option value="Comprensión lectora">Comprensión lectora</option>
                        <option value="Inglés">Inglés</option>
                        <option value="Computación">Computación</option>
                        <option value="Programación">Programación</option>
                        <option value="Robótica">Robótica</option>
                        <option value="Ajedrez">Ajedrez</option>
                        <option value="Cubo Rubik" selected>Cubo Rubik</option>
                        <option value="Preparación para exámenes">Preparación para exámenes</option>
                    </select>
                    <button class="winner-save" type="submit">Anotar</button>
                </form>
                <div class="winner-list" id="winnerList" aria-live="polite"></div>
            </section>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        const PASS_SCORE = {{ $passScore }};
        const liveShell = document.getElementById('liveShell');
        const firstTerm = document.getElementById('firstTerm');
        const secondTerm = document.getElementById('secondTerm');
        const thirdTerm = document.getElementById('thirdTerm');
        const thirdOverLabel = document.getElementById('thirdOverLabel');
        const secondRange = document.getElementById('secondRange');
        const cardFirst = document.getElementById('cardFirst');
        const cardSecond = document.getElementById('cardSecond');
        const cardThird = document.getElementById('cardThird');
        const progressFill = document.getElementById('progressFill');
        const progressMarker = document.getElementById('progressMarker');
        const totalScore = document.getElementById('totalScore');
        const statusTitle = document.getElementById('statusTitle');
        const missingPoints = document.getElementById('missingPoints');
        const targetSecond = document.getElementById('targetSecond');
        const targetThird = document.getElementById('targetThird');
        const helperText = document.getElementById('helperText');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const prizeOpen = document.getElementById('prizeOpen');
        const prizeModal = document.getElementById('prizeModal');
        const prizeClose = document.getElementById('prizeClose');
        const prizeSpin = document.getElementById('prizeSpin');
        const prizeNumber = document.getElementById('prizeNumber');
        const winnerForm = document.getElementById('winnerForm');
        const winnerName = document.getElementById('winnerName');
        const winnerPrize = document.getElementById('winnerPrize');
        const winnerList = document.getElementById('winnerList');
        const PRIZE_SPIN_MS = 2000;
        const PRIZE_MAX_NUMBER = 29;
        const WINNERS_STORAGE_KEY = 'ifeLiveWinners';
        const WINNERS_URL = '{{ route('live.winners.index') }}';
        let prizeSpinTimer = null;
        let currentPrizeNumber = null;
        let winners = [];
        let lastSecond = Number(secondRange.value);
        let lastThird = Number(thirdTerm.textContent);

        function sanitize(input){
            const clean = String(input.value || '').replace(/\D/g, '').slice(0, 3);
            if (clean === '') {
                input.value = '0';
                return 0;
            }

            const value = Math.min(100, Number(clean));
            input.value = String(value);
            return value;
        }

        function statusForThird(thirdNeeded){
            if (thirdNeeded <= 70) return 'ok';
            if (thirdNeeded <= 100) return 'warning';
            return 'risk';
        }

        function stateText(thirdNeeded){
            if (thirdNeeded <= 0) return 'Ya alcanza';
            if (thirdNeeded <= 70) return 'Aprueba';
            if (thirdNeeded <= 100) return 'En riesgo';
            return 'Reprueba';
        }

        function setTheme(kind){
            const colors = {
                ok: ['#16a34a', 'rgba(22,163,74,.13)', 'rgba(22,163,74,.32)'],
                warning: ['#f59e0b', 'rgba(245,158,11,.15)', 'rgba(245,158,11,.36)'],
                risk: ['#ef4444', 'rgba(239,68,68,.13)', 'rgba(239,68,68,.32)']
            };
            const [state, soft, shadow] = colors[kind] || colors.warning;
            document.documentElement.style.setProperty('--state', state);
            document.documentElement.style.setProperty('--state-soft', soft);
            document.documentElement.style.setProperty('--state-shadow', shadow);
        }

        function splitRemaining(first){
            const remaining = Math.max(0, PASS_SCORE - first);
            return [Math.ceil(remaining / 2), Math.floor(remaining / 2)];
        }

        function flash(element, className = 'changed'){
            element.classList.remove(className);
            void element.offsetWidth;
            element.classList.add(className);
        }

        function applyCardState(card, value){
            const kind = value >= 51 ? 'ok' : (value >= 40 ? 'warning' : 'risk');
            card.classList.add('filled');
            card.classList.remove('ok', 'warning', 'risk');
            card.classList.add(kind);
        }

        function update(changedElement = null){
            const first = sanitize(firstTerm);
            const [defaultSecond] = splitRemaining(first);
            const second = Math.max(0, Math.min(100, Number(secondRange.value || defaultSecond)));
            const thirdNeeded = Math.max(0, PASS_SCORE - first - second);
            const shownThird = thirdNeeded > 100 ? '100' : String(thirdNeeded);
            const missingAfterFirst = Math.max(0, PASS_SCORE - first);
            const percent = second;
            const kind = statusForThird(thirdNeeded);
            const displayTotal = first + second + Math.min(100, thirdNeeded);

            setTheme(kind);
            progressFill.style.width = `${percent}%`;
            progressMarker.style.left = `${percent}%`;
            totalScore.textContent = displayTotal;
            statusTitle.textContent = stateText(thirdNeeded);
            secondTerm.textContent = second;
            thirdTerm.textContent = shownThird;
            thirdOverLabel.classList.toggle('show', thirdNeeded > 100);
            missingPoints.textContent = missingAfterFirst;
            targetSecond.textContent = second;
            targetThird.textContent = shownThird;
            helperText.textContent = thirdNeeded > 100
                ? `Con primer trimestre ${first} y segundo ${second}, ni sacando 100 en tercero llega a 153.`
                : `Con primer trimestre ${first}, segundo ${second} y tercero ${shownThird} suma 153.`;

            applyCardState(cardFirst, first);
            applyCardState(cardSecond, second);
            cardThird.classList.add('filled');
            cardThird.classList.remove('ok', 'warning', 'risk');
            cardThird.classList.add(kind);

            if (changedElement) {
                flash(changedElement);
                flash(progressMarker);
                flash(liveShell, 'flash');
            } else if (second !== lastSecond || thirdNeeded !== lastThird) {
                flash(progressMarker);
            }

            if (second !== lastSecond) flash(secondTerm);
            if (thirdNeeded !== lastThird) flash(thirdTerm);
            lastSecond = second;
            lastThird = thirdNeeded;
        }

        function resetScenarioFromFirst(){
            const first = sanitize(firstTerm);
            const [defaultSecond] = splitRemaining(first);
            secondRange.value = Math.min(100, defaultSecond);
            update(cardFirst);
        }

        function randomPrizeNumber(){
            return Math.floor(Math.random() * PRIZE_MAX_NUMBER) + 1;
        }

        function loadWinners(){
            try {
                const saved = JSON.parse(localStorage.getItem(WINNERS_STORAGE_KEY) || '[]');
                winners = Array.isArray(saved) ? saved : [];
            } catch (error) {
                winners = [];
            }
        }

        function saveWinners(){
            localStorage.setItem(WINNERS_STORAGE_KEY, JSON.stringify(winners.slice(0, 40)));
        }

        async function syncWinners(){
            try {
                const response = await fetch(WINNERS_URL, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const payload = await response.json();
                if (Array.isArray(payload.winners)) {
                    winners = payload.winners;
                    saveWinners();
                    renderWinners();
                }
            } catch (error) {
                renderWinners();
            }
        }

        function renderWinners(){
            if (!winners.length) {
                winnerList.innerHTML = '<p class="winner-empty">Todavia no hay ganadores anotados.</p>';
                return;
            }

            winnerList.innerHTML = winners.map(winner => {
                const number = winner.number ? `#${winner.number}` : '#?';
                return `
                    <article class="winner-item">
                        <div class="winner-number">${number}</div>
                        <div class="winner-detail">
                            <strong>${escapeHtml(winner.name)}</strong>
                            <span>${escapeHtml(winner.prize)} - ${escapeHtml(winner.date)}</span>
                        </div>
                    </article>
                `;
            }).join('');
        }

        function escapeHtml(value){
            return String(value).replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character]));
        }

        async function addWinner(event){
            event.preventDefault();

            const name = winnerName.value.trim();
            const prize = winnerPrize.value || 'Cubo Rubik';
            if (!name) {
                winnerName.focus();
                return;
            }

            const draftWinner = {
                name,
                prize,
                number: currentPrizeNumber,
                date: new Date().toLocaleString('es-BO', {
                    day: '2-digit',
                    month: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            };

            try {
                const response = await fetch(WINNERS_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        winner_name: name,
                        prize,
                        drawn_number: currentPrizeNumber
                    })
                });

                if (!response.ok) {
                    throw new Error('No se pudo guardar en la base de datos.');
                }

                const payload = await response.json();
                winners.unshift(payload.winner || draftWinner);
            } catch (error) {
                winners.unshift(draftWinner);
            }

            winners = winners.slice(0, 40);
            saveWinners();
            renderWinners();
            winnerName.value = '';
            winnerName.focus();
        }

        function openPrizeModal(){
            prizeModal.hidden = false;
            prizeNumber.textContent = '?';
            currentPrizeNumber = null;
            prizeNumber.classList.remove('spinning', 'done');
            prizeSpin.classList.remove('spinning');
            prizeSpin.disabled = false;
            renderWinners();
            requestAnimationFrame(() => {
                prizeModal.classList.add('open');
                prizeSpin.focus();
            });
        }

        function closePrizeModal(){
            if (prizeSpinTimer) {
                window.clearInterval(prizeSpinTimer);
                prizeSpinTimer = null;
            }
            prizeSpin.disabled = false;
            prizeSpin.classList.remove('spinning');
            prizeNumber.classList.remove('spinning');
            prizeModal.classList.remove('open');
            window.setTimeout(() => {
                if (!prizeModal.classList.contains('open')) {
                    prizeModal.hidden = true;
                }
            }, 280);
        }

        function spinPrizeNumber(){
            if (prizeSpin.disabled) return;

            const startedAt = Date.now();
            prizeSpin.disabled = true;
            prizeSpin.classList.add('spinning');
            prizeNumber.classList.remove('done');
            prizeNumber.classList.add('spinning');

            prizeSpinTimer = window.setInterval(() => {
                prizeNumber.textContent = randomPrizeNumber();
                if (Date.now() - startedAt >= PRIZE_SPIN_MS) {
                    window.clearInterval(prizeSpinTimer);
                    prizeSpinTimer = null;
                    currentPrizeNumber = randomPrizeNumber();
                    prizeNumber.textContent = currentPrizeNumber;
                    prizeNumber.classList.remove('spinning');
                    prizeNumber.classList.add('done');
                    prizeSpin.classList.remove('spinning');
                    prizeSpin.disabled = false;
                    prizeSpin.focus();
                }
            }, 62);
        }

        firstTerm.addEventListener('input', resetScenarioFromFirst);
        firstTerm.addEventListener('change', resetScenarioFromFirst);
        firstTerm.addEventListener('focus', () => firstTerm.select());
        secondRange.addEventListener('input', () => update(cardSecond));
        secondRange.addEventListener('change', () => update(cardSecond));
        progressMarker.addEventListener('animationend', () => {
            progressMarker.classList.remove('changed');
        });
        prizeOpen.addEventListener('click', openPrizeModal);
        prizeClose.addEventListener('click', closePrizeModal);
        prizeSpin.addEventListener('click', spinPrizeNumber);
        winnerForm.addEventListener('submit', addWinner);
        prizeModal.addEventListener('click', event => {
            if (event.target === prizeModal) closePrizeModal();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !prizeModal.hidden) closePrizeModal();
        });

        update();
        if (window.jQuery && jQuery.fn.select2) {
            jQuery(winnerPrize).select2({
                dropdownParent: jQuery('#prizeModal'),
                placeholder: 'Selecciona premio',
                width: '100%'
            });
        }
        loadWinners();
        renderWinners();
        syncWinners();
    </script>
</body>
</html>
