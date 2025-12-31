<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>스토어 디자인 Final v5 (Smart Autoplay)</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Pretendard', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            orange: '#F97316', // 사이트 테마 컬러
                            dark: '#1F2937',
                            cardBg: '#ebe8e0', // 요청하신 기본 배경색 (#ebe8e0)
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css');
        
        /* 글라스 캡슐 버튼 */
        .glass-capsule {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            transition: all 0.3s ease;
        }
        
        .group:hover .glass-capsule {
            background: rgba(255, 255, 255, 0.6);
            padding-right: 6px;
        }

        /* 이미지 줌 효과 */
        .img-zoom {
            transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .group:hover .img-zoom {
            transform: scale(1.1);
        }

        /* Swiper 네비게이션 커스텀 */
        .swiper-button-next, .swiper-button-prev {
            color: #111 !important;
            width: 40px !important;
            height: 40px !important;
            background: rgba(255,255,255,0.8);
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            backdrop-filter: blur(4px);
            opacity: 0; /* 평소엔 숨김 */
            transition: opacity 0.3s ease;
        }
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 16px !important;
            font-weight: bold;
        }
        .swiper-button-disabled {
            opacity: 0 !important;
        }

        /* 마우스 올렸을 때만 네비게이션 보이기 */
        .group\/section:hover .swiper-button-next,
        .group\/section:hover .swiper-button-prev {
            opacity: 1;
        }

        /* 텍스트 줄임 */
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* 색상 전환 부드럽게 */
        .transition-colors-custom {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white font-sans">

<div id="bo_gall" class="w-full max-w-[1800px] mx-auto px-4 py-16">

    <!-- 메인 타이틀 -->
    <div class="mb-16 text-center">
        <h2 class="text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">STORE</h2>
        <p class="text-gray-500 text-lg">공간을 완성하는 특별한 디자인 간판</p>
    </div>

    <!-- ==============================================
         [SECTION 1] LED 채널 (Smart Autoplay)
         ============================================== -->
    <section class="mb-20 relative group/section">
        <!-- 1. 섹션 헤더 -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 px-2">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-brand-orange text-white text-[10px] font-bold rounded-md">BEST</span>
                    <h3 class="text-3xl font-extrabold text-gray-900">LED Channel</h3>
                </div>
                <p class="text-gray-500 text-sm md:text-base">밤에도 선명하게 빛나는 최고급 사양의 LED 간판 모음입니다.</p>
            </div>
            <a href="#" class="hidden md:flex items-center text-sm font-bold text-gray-500 hover:text-brand-orange transition-colors mt-4 md:mt-0">
                전체보기 <i class="fa fa-chevron-right text-xs ml-2"></i>
            </a>
        </div>

        <!-- 2. Swiper 슬라이더 -->
        <div class="swiper mySwiper1 px-2 pb-10">
            <div class="swiper-wrapper">
                
                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">PREMIUM</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">에폭시 채널</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">매끈한 마감의 고급형</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">450,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1567427018141-0584cfcbf1b8?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">POPULAR</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">알루미늄 채널</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">가성비 최고의 선택</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">350,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1563461660947-507ef49e9c47?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">NEW</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">일체형 채널</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">군더더기 없는 깔끔함</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">420,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1550608518-28303f90b14b?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">HOT</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">후광 채널</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">은은한 분위기 연출</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">380,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 5 (More) -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">BASIC</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">고무 스카시</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">가볍고 튼튼한 소재</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">상담문의</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white flex items-center justify-center border border-dashed border-gray-300">
                             <div class="text-center text-gray-400">
                                <i class="fa fa-plus text-4xl mb-2"></i>
                                <p class="text-xs font-bold">더 보기</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- 네비게이션 화살표 -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        
        <!-- 모바일용 더보기 버튼 -->
        <div class="md:hidden text-center mt-4">
            <a href="#" class="inline-block px-6 py-3 border border-gray-300 rounded-full text-sm font-bold text-gray-700">LED 채널 전체보기</a>
        </div>
    </section>

    <!-- 분류선 -->
    <hr class="border-t border-gray-200 my-16">

    <!-- ==============================================
         [SECTION 2] 포인트 간판 (Smart Autoplay)
         ============================================== -->
    <section class="mb-12 relative group/section">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 px-2">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-black text-white text-[10px] font-bold rounded-md">HOT</span>
                    <h3 class="text-3xl font-extrabold text-gray-900">Point Sign</h3>
                </div>
                <p class="text-gray-500 text-sm md:text-base">매장의 개성을 살려주는 큐브 및 돌출 간판입니다.</p>
            </div>
            <a href="#" class="hidden md:flex items-center text-sm font-bold text-gray-500 hover:text-brand-orange transition-colors mt-4 md:mt-0">
                전체보기 <i class="fa fa-chevron-right text-xs ml-2"></i>
            </a>
        </div>

        <div class="swiper mySwiper2 px-2 pb-10">
            <div class="swiper-wrapper">
                
                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">CUBE</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">큐브 간판</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">3면이 빛나는 포인트</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">150,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1555447405-058428d1ac79?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">CIRCLE</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">원형 돌출</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">가장 기본적인 디자인</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">120,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1524312686702-861f22497fb2?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                 <!-- Slide 3 -->
                 <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">ART</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">철제 부식</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">빈티지한 매력</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">180,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1516961642265-531546e84af2?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Slide 4 -->
                 <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">WOOD</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">우드 현판</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">따뜻한 나무 감성</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">90,000</span><span class="text-sm text-gray-600 font-medium group-hover:text-white/90 transition-colors">원</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white shadow-inner">
                            <img src="https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=600&auto=format&fit=crop" alt="상품" class="w-full h-full object-cover img-zoom">
                            <div class="absolute bottom-4 left-4">
                                <div class="glass-capsule h-10 pl-4 pr-1 rounded-full flex items-center gap-2">
                                    <span class="text-white text-xs font-bold drop-shadow-md">자세히 보기</span>
                                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-black shadow-sm"><i class="fa fa-arrow-right text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Slide 5 -->
                 <div class="swiper-slide">
                    <div class="group relative flex flex-col h-[500px] bg-brand-cardBg hover:bg-brand-orange rounded-[2rem] overflow-hidden transition-colors-custom duration-300 cursor-pointer shadow-sm hover:shadow-xl border border-transparent">
                        <div class="h-[35%] px-6 py-5 flex flex-col justify-between relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="px-2.5 py-1 bg-white rounded-full text-[10px] font-bold text-gray-900 uppercase tracking-wider border border-gray-200">CUSTOM</span>
                            </div>
                            <div class="mt-2">
                                <h3 class="text-2xl font-extrabold text-gray-900 leading-tight mb-1 group-hover:text-white transition-colors">주문 제작</h3>
                                <p class="text-xs text-gray-600 font-medium group-hover:text-white/90 transition-colors">나만의 디자인</p>
                            </div>
                            <div class="mt-auto pt-2 border-t border-gray-300 group-hover:border-white/30 transition-colors">
                                <span class="text-xl font-black text-gray-900 group-hover:text-white transition-colors">상담문의</span>
                            </div>
                        </div>
                        <div class="h-[65%] relative overflow-hidden rounded-[2rem] mx-2 mb-2 bg-white flex items-center justify-center border border-dashed border-gray-300">
                             <div class="text-center text-gray-400">
                                <i class="fa fa-plus text-4xl mb-2"></i>
                                <p class="text-xs font-bold">더 보기</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- 네비게이션 화살표 -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
         
         <div class="md:hidden text-center mt-4">
            <a href="#" class="inline-block px-6 py-3 border border-gray-300 rounded-full text-sm font-bold text-gray-700">Point Sign 전체보기</a>
        </div>
    </section>

</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    // Swiper 공통 설정
    const swiperConfig = {
        slidesPerView: 1.2, // 모바일에서 옆에 살짝 보이게
        spaceBetween: 20,
        autoplay: {
            delay: 2500, // 2.5초마다 넘어가게
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            640: {
                slidesPerView: 2.2, // 태블릿
                spaceBetween: 20,
            },
            1024: {
                slidesPerView: 3.2, 
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 4, // PC에서 4개
                spaceBetween: 30,
            },
        },
    };

    // Swiper 초기화
    const swiper1 = new Swiper(".mySwiper1", swiperConfig);
    const swiper2 = new Swiper(".mySwiper2", swiperConfig);

    // 평소엔 멈춰있다가 (stop), 호버 시 움직이게 (start) 설정
    swiper1.autoplay.stop();
    swiper2.autoplay.stop();

    function bindHoverAutoplay(swiper, selector) {
        const container = document.querySelector(selector);
        
        container.addEventListener('mouseenter', () => {
            swiper.autoplay.start();
        });
        
        container.addEventListener('mouseleave', () => {
            swiper.autoplay.stop();
        });
    }

    // 각 섹션에 호버 이벤트 바인딩
    bindHoverAutoplay(swiper1, ".mySwiper1");
    bindHoverAutoplay(swiper2, ".mySwiper2");

</script>

</body>
</html>