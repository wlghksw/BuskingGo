import React, { useState, useEffect } from 'react';
import { Music, MapPin, User, Calendar, Clock, Search, Heart, Star, Filter, Navigation, Menu, X, Plus, ChevronRight, DollarSign, Users } from 'lucide-react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Leaflet 마커 아이콘 설정 (기본 아이콘 경로 문제 해결)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

const BuskingGo = () => {
  const [currentPage, setCurrentPage] = useState('home');
  const [selectedPerformance, setSelectedPerformance] = useState(null);
  const [userLocation, setUserLocation] = useState({ lat: 36.8151, lng: 127.1139 }); // 천안
  const [searchRadius, setSearchRadius] = useState(5);
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [favorites, setFavorites] = useState([]);

  // 샘플 공연 데이터
  const [performances, setPerformances] = useState([
    {
      id: 1,
      buskerName: "어쿠스틱 소울",
      location: "천안역 광장",
      lat: 36.8151,
      lng: 127.1139,
      startTime: "18:00",
      endTime: "20:00",
      status: "진행중",
      image: "🎸",
      rating: 4.8,
      distance: 0.5,
      description: "감성 넘치는 어쿠스틱 공연"
    },
    {
      id: 2,
      buskerName: "재즈 트리오",
      location: "신세계 백화점 앞",
      lat: 36.8100,
      lng: 127.1200,
      startTime: "19:00",
      endTime: "21:00",
      status: "예정",
      image: "🎺",
      rating: 4.9,
      distance: 1.2,
      description: "재즈의 매력에 빠져보세요"
    },
    {
      id: 3,
      buskerName: "힙합 크루",
      location: "백석대학교 광장",
      lat: 36.8000,
      lng: 127.1050,
      startTime: "20:00",
      endTime: "22:00",
      status: "예정",
      image: "🎤",
      rating: 4.7,
      distance: 2.1,
      description: "열정 가득한 힙합 퍼포먼스"
    }
  ]);

  // 버스커 등록 폼 상태
  const [buskerForm, setBuskerForm] = useState({
    name: '',
    teamSize: 1,
    equipment: '',
    phone: '',
    bio: '',
    availableDays: [],
    preferredTime: ''
  });

  // 공연 예약 폼 상태
  const [bookingForm, setBookingForm] = useState({
    organizerName: '',
    organizerType: '',
    location: '',
    date: '',
    startTime: '',
    endTime: '',
    additionalRequest: ''
  });

  const days = ['월', '화', '수', '목', '금', '토', '일'];

  const filteredPerformances = performances;

  const toggleFavorite = (id) => {
    setFavorites(prev => 
      prev.includes(id) ? prev.filter(fid => fid !== id) : [...prev, id]
    );
  };

  // 커스텀 마커 아이콘 생성 함수
  const createCustomIcon = (performance) => {
    const isLive = performance.status === '진행중';
    return L.divIcon({
      className: 'custom-marker',
      html: `
        <div style="
          background: ${isLive ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #9333ea, #7c3aed)'};
          border: 3px solid ${isLive ? '#ffffff' : '#ffffff'};
          border-radius: 50%;
          width: 50px;
          height: 50px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 28px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
          transition: transform 0.2s;
        ">
          🎤
        </div>
      `,
      iconSize: [50, 50],
      iconAnchor: [25, 25],
      popupAnchor: [0, -25],
    });
  };

  // 메인 페이지
  const HomePage = () => (
    <div className="space-y-6">
      {/* 히어로 섹션 */}
      <div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-8 text-white">
        <h1 className="text-4xl font-bold mb-4">내 주변 버스킹 찾기 🎵</h1>
        <p className="text-xl mb-6">지금 진행 중인 공연을 확인하세요</p>
        
        <div className="flex gap-4 flex-wrap">
          <div className="bg-white/20 backdrop-blur-lg rounded-xl p-4 flex items-center gap-3">
            <Navigation className="text-white" size={24} />
            <div>
              <p className="text-sm opacity-80">현재 위치</p>
              <p className="font-bold">천안역 광장</p>
            </div>
          </div>
          <div className="bg-white/20 backdrop-blur-lg rounded-xl p-4 flex items-center gap-3">
            <Clock className="text-white" size={24} />
            <div>
              <p className="text-sm opacity-80">진행중 공연</p>
              <p className="font-bold text-2xl">3개</p>
            </div>
          </div>
        </div>
      </div>

      {/* 지도 섹션 */}
      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <h2 className="text-2xl font-bold mb-4">실시간 공연 지도</h2>
        <div className="rounded-xl overflow-hidden border border-gray-700" style={{ height: '400px' }}>
          <MapContainer
            center={[userLocation.lat, userLocation.lng]}
            zoom={13}
            style={{ height: '100%', width: '100%' }}
            className="z-0"
          >
            <TileLayer
              attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />
            {filteredPerformances.map(perf => (
              <Marker
                key={perf.id}
                position={[perf.lat, perf.lng]}
                icon={createCustomIcon(perf)}
                eventHandlers={{
                  click: () => setSelectedPerformance(perf),
                }}
              >
                <Popup>
                  <div className="text-gray-800">
                    <div className="text-2xl mb-2">{perf.image}</div>
                    <h3 className="font-bold text-lg mb-1">{perf.buskerName}</h3>
                    <p className="text-xs text-gray-500 mb-1">
                      <MapPin size={12} className="inline mr-1" />
                      {perf.location}
                    </p>
                    <p className="text-xs text-gray-500 mb-1">
                      <Clock size={12} className="inline mr-1" />
                      {perf.startTime} - {perf.endTime}
                    </p>
                    {perf.status === '진행중' && (
                      <span className="inline-block px-2 py-1 bg-red-500 text-white text-xs rounded-full mt-2">
                        LIVE
                      </span>
                    )}
                  </div>
                </Popup>
              </Marker>
            ))}
          </MapContainer>
        </div>
      </div>

      {/* 공연 목록 */}
      <div className="space-y-4">
        {filteredPerformances.map(perf => (
          <div
            key={perf.id}
            onClick={() => setSelectedPerformance(perf)}
            className="bg-gray-800 rounded-2xl p-6 hover:bg-gray-750 transition-all cursor-pointer border border-gray-700 hover:border-purple-500"
          >
            <div className="flex items-start justify-between mb-4">
              <div className="flex items-center gap-4">
                <div className="text-5xl">{perf.image}</div>
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="text-2xl font-bold">{perf.buskerName}</h3>
                    {perf.status === '진행중' && (
                      <span className="px-3 py-1 bg-red-500 text-white text-sm rounded-full animate-pulse">
                        LIVE
                      </span>
                    )}
                  </div>
                </div>
              </div>
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  toggleFavorite(perf.id);
                }}
                className="p-2 hover:bg-gray-700 rounded-full transition-all"
              >
                <Heart
                  size={24}
                  className={favorites.includes(perf.id) ? 'fill-red-500 text-red-500' : 'text-gray-400'}
                />
              </button>
            </div>

            <div className="grid grid-cols-2 gap-4 mb-4">
              <div className="flex items-center gap-2 text-gray-300">
                <MapPin size={18} />
                <span>{perf.location}</span>
              </div>
              <div className="flex items-center gap-2 text-gray-300">
                <Clock size={18} />
                <span>{perf.startTime} - {perf.endTime}</span>
              </div>
              <div className="flex items-center gap-2 text-gray-300">
                <Navigation size={18} />
                <span>{perf.distance}km</span>
              </div>
              <div className="flex items-center gap-2 text-yellow-400">
                <Star size={18} fill="currentColor" />
                <span>{perf.rating}</span>
              </div>
            </div>

            <p className="text-gray-400 mb-4">{perf.description}</p>
          </div>
        ))}
      </div>
    </div>
  );

  // 버스커 등록 페이지
  const BuskerRegisterPage = () => (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
        <h2 className="text-3xl font-bold mb-2">버스커 등록</h2>
        <p>프로필을 등록하고 공연 기회를 받아보세요</p>
      </div>

      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <form className="space-y-6">
          {/* 기본 정보 */}
          <div>
            <label className="block text-sm font-bold mb-2">팀/개인명 *</label>
            <input
              type="text"
              value={buskerForm.name}
              onChange={(e) => setBuskerForm({...buskerForm, name: e.target.value})}
              placeholder="예: 어쿠스틱 소울"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">팀 인원</label>
              <input
                type="number"
                min="1"
                value={buskerForm.teamSize}
                onChange={(e) => setBuskerForm({...buskerForm, teamSize: Number(e.target.value)})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
              />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">보유 장비</label>
            <input
              type="text"
              value={buskerForm.equipment}
              onChange={(e) => setBuskerForm({...buskerForm, equipment: e.target.value})}
              placeholder="예: 기타, 앰프, 마이크"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">연락처 *</label>
            <input
              type="tel"
              value={buskerForm.phone}
              onChange={(e) => setBuskerForm({...buskerForm, phone: e.target.value})}
              placeholder="010-0000-0000"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">소개</label>
            <textarea
              value={buskerForm.bio}
              onChange={(e) => setBuskerForm({...buskerForm, bio: e.target.value})}
              placeholder="팀 소개 및 공연 스타일을 자유롭게 작성해주세요"
              rows="4"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          {/* 공연 가능 요일 */}
          <div>
            <label className="block text-sm font-bold mb-2">공연 가능 요일</label>
            <div className="flex gap-2 flex-wrap">
              {days.map(day => (
                <button
                  key={day}
                  type="button"
                  onClick={() => {
                    const newDays = buskerForm.availableDays.includes(day)
                      ? buskerForm.availableDays.filter(d => d !== day)
                      : [...buskerForm.availableDays, day];
                    setBuskerForm({...buskerForm, availableDays: newDays});
                  }}
                  className={`px-4 py-2 rounded-lg transition-all ${
                    buskerForm.availableDays.includes(day)
                      ? 'bg-purple-600 text-white'
                      : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                  }`}
                >
                  {day}
                </button>
              ))}
            </div>
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">선호 시간대</label>
            <select
              value={buskerForm.preferredTime}
              onChange={(e) => setBuskerForm({...buskerForm, preferredTime: e.target.value})}
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            >
              <option value="">선택하세요</option>
              <option value="오후">오후 (14:00-18:00)</option>
              <option value="저녁">저녁 (18:00-22:00)</option>
              <option value="야간">야간 (22:00-24:00)</option>
            </select>
          </div>

          <button
            type="submit"
            className="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform"
          >
            등록 완료
          </button>
        </form>
      </div>
    </div>
  );

  // 공연 예약 페이지
  const BookingPage = () => (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-6 text-white">
        <h2 className="text-3xl font-bold mb-2">공연 예약</h2>
        <p>행사에 필요한 공연을 예약하세요</p>
      </div>

      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <form className="space-y-6">
          <div>
            <label className="block text-sm font-bold mb-2">주최자명 *</label>
            <input
              type="text"
              value={bookingForm.organizerName}
              onChange={(e) => setBookingForm({...bookingForm, organizerName: e.target.value})}
              placeholder="예: 천안시청, 백석대학교"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">주최자 유형 *</label>
            <select
              value={bookingForm.organizerType}
              onChange={(e) => setBookingForm({...bookingForm, organizerType: e.target.value})}
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            >
              <option value="">선택하세요</option>
              <option value="지자체">지자체</option>
              <option value="대학">대학교</option>
              <option value="축제">축제 운영사</option>
              <option value="상권">상권조합</option>
              <option value="기타">기타</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">공연 장소 *</label>
            <input
              type="text"
              value={bookingForm.location}
              onChange={(e) => setBookingForm({...bookingForm, location: e.target.value})}
              placeholder="예: 천안역 광장"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">공연 날짜 *</label>
            <input
              type="date"
              value={bookingForm.date}
              onChange={(e) => setBookingForm({...bookingForm, date: e.target.value})}
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <div className="grid md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-bold mb-2">시작 시간 *</label>
              <input
                type="time"
                value={bookingForm.startTime}
                onChange={(e) => setBookingForm({...bookingForm, startTime: e.target.value})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
              />
            </div>

            <div>
              <label className="block text-sm font-bold mb-2">종료 시간 *</label>
              <input
                type="time"
                value={bookingForm.endTime}
                onChange={(e) => setBookingForm({...bookingForm, endTime: e.target.value})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-bold mb-2">추가 요청사항</label>
            <textarea
              value={bookingForm.additionalRequest}
              onChange={(e) => setBookingForm({...bookingForm, additionalRequest: e.target.value})}
              placeholder="특별한 요청사항이 있으시면 작성해주세요"
              rows="4"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500"
            />
          </div>

          <button
            type="submit"
            className="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold py-4 rounded-lg hover:scale-105 transition-transform"
          >
            예약 신청하기
          </button>
        </form>
      </div>

      {/* 매칭 프로세스 안내 */}
      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700">
        <h3 className="text-xl font-bold mb-4">매칭 프로세스</h3>
        <div className="space-y-3">
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center font-bold">1</div>
            <p className="text-gray-300">예약 정보 등록</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center font-bold">2</div>
            <p className="text-gray-300">조건에 맞는 버스커 자동 추천</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center font-bold">3</div>
            <p className="text-gray-300">버스커 선택 및 계약</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center font-bold">4</div>
            <p className="text-gray-300">공연 진행 및 자동 정산</p>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-b from-gray-900 via-slate-900 to-gray-900 text-white">
      {/* 헤더 */}
      <header className="sticky top-0 z-40 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Music className="text-purple-500" size={32} />
            <h1 className="text-2xl font-bold">버스킹고</h1>
          </div>

          {/* 데스크톱 네비게이션 */}
          <nav className="hidden md:flex gap-6">
            <button
              onClick={() => setCurrentPage('home')}
              className={`font-bold transition-colors ${
                currentPage === 'home' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
            >
              공연 찾기
            </button>
            <button
              onClick={() => setCurrentPage('register')}
              className={`font-bold transition-colors ${
                currentPage === 'register' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
            >
              버스커 등록
            </button>
            <button
              onClick={() => setCurrentPage('booking')}
              className={`font-bold transition-colors ${
                currentPage === 'booking' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
            >
              공연 예약
            </button>
          </nav>

          {/* 모바일 메뉴 버튼 */}
          <button 
            className="md:hidden p-2 hover:bg-gray-800 rounded-lg"
            onClick={() => setIsMenuOpen(!isMenuOpen)}
          >
            <Menu size={24} />
          </button>
        </div>

        {/* 모바일 메뉴 */}
        {isMenuOpen && (
          <div className="md:hidden bg-gray-800 border-t border-gray-700">
            <nav className="flex flex-col">
              <button
                onClick={() => { setCurrentPage('home'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors"
              >
                공연 찾기
              </button>
              <button
                onClick={() => { setCurrentPage('register'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors"
              >
                버스커 등록
              </button>
              <button
                onClick={() => { setCurrentPage('booking'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors"
              >
                공연 예약
              </button>
            </nav>
          </div>
        )}
      </header>

      {/* 메인 컨텐츠 */}
      <main className="max-w-6xl mx-auto px-4 py-8">
        {currentPage === 'home' && <HomePage />}
        {currentPage === 'register' && <BuskerRegisterPage />}
        {currentPage === 'booking' && <BookingPage />}
      </main>

      {/* 공연 상세 모달 */}
      {selectedPerformance && (
        <div className="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onClick={() => setSelectedPerformance(null)}>
          <div className="bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700" onClick={(e) => e.stopPropagation()}>
            <div className="p-6">
              <div className="flex items-start justify-between mb-6">
                <div className="flex items-center gap-4">
                  <div className="text-6xl">{selectedPerformance.image}</div>
                  <div>
                    <h2 className="text-3xl font-bold mb-2">{selectedPerformance.buskerName}</h2>
                  </div>
                </div>
                <button onClick={() => setSelectedPerformance(null)} className="p-2 hover:bg-gray-700 rounded-full">
                  <X size={24} />
                </button>
              </div>

              <div className="space-y-6">
                <div className="bg-gray-900 rounded-xl p-4">
                  <h3 className="font-bold mb-3">공연 정보</h3>
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <MapPin size={18} className="text-purple-400" />
                      <span>{selectedPerformance.location}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Clock size={18} className="text-purple-400" />
                      <span>{selectedPerformance.startTime} - {selectedPerformance.endTime}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Navigation size={18} className="text-purple-400" />
                      <span>현재 위치에서 {selectedPerformance.distance}km</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Star size={18} className="text-yellow-400" fill="currentColor" />
                      <span>{selectedPerformance.rating} / 5.0</span>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 className="font-bold mb-3">공연 소개</h3>
                  <p className="text-gray-300">{selectedPerformance.description}</p>
                </div>

                <div className="flex gap-3">
                  <button className="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-3 rounded-lg hover:scale-105 transition-transform">
                    길찾기
                  </button>
                  <button 
                    onClick={() => toggleFavorite(selectedPerformance.id)}
                    className="px-6 py-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-all"
                  >
                    <Heart 
                      size={24} 
                      className={favorites.includes(selectedPerformance.id) ? 'fill-red-500 text-red-500' : 'text-gray-400'}
                    />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BuskingGo;
