import React, { useState, useEffect, useMemo, useRef } from 'react';
import { Music, MapPin, User, Calendar, Clock, Search, Heart, Star, Filter, Navigation, Menu, X, Plus, ChevronRight, DollarSign, Users, QrCode, Bell, MessageSquare, Building2, Store, Shield, FileText, Megaphone, AlertCircle } from 'lucide-react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
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
  const [userType, setUserType] = useState(null); // null: 미로그인, 'viewer': 관람자, 'artist': 아티스트, 'business': 상업공간, 'organization': 기관
  const [showUserTypeSelect, setShowUserTypeSelect] = useState(false);
  const [selectedLocation, setSelectedLocation] = useState(''); // 선택된 지역명

  // 지역 좌표 매핑 (백업용)
  const locationCoordinates = {
    '천안': { lat: 36.8151, lng: 127.1139 },
    '서울': { lat: 37.5665, lng: 126.9780 },
    '부산': { lat: 35.1796, lng: 129.0756 },
    '대구': { lat: 35.8714, lng: 128.6014 },
    '인천': { lat: 37.4563, lng: 126.7052 },
    '광주': { lat: 35.1595, lng: 126.8526 },
    '대전': { lat: 36.3504, lng: 127.3845 },
    '울산': { lat: 35.5384, lng: 129.3114 },
    '수원': { lat: 37.2636, lng: 127.0286 },
    '성남': { lat: 37.4201, lng: 127.1267 },
  };

  // 지역 선택 함수
  const handleLocationSelect = (locationName) => {
    if (!locationName) {
      // 지역이 선택되지 않으면 초기화
      setSelectedLocation('');
      setUserLocation({ lat: 36.8151, lng: 127.1139 });
      return;
    }
    
    // 선택된 지역명 저장
    setSelectedLocation(locationName);
    
    // 좌표 매핑에서 좌표 가져오기
    const location = locationCoordinates[locationName] || { lat: 36.8151, lng: 127.1139 };
    setUserLocation(location);
  };


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

  // 지역 필터링
  const filteredPerformances = useMemo(() => {
    if (selectedLocation) {
      return performances.filter(perf => {
        const locationLower = perf.location.toLowerCase();
        const selectedLower = selectedLocation.toLowerCase();
        // 선택된 지역명이 위치에 포함되어 있으면 표시
        return locationLower.includes(selectedLower);
      });
    }
    return performances;
  }, [selectedLocation, performances]);

  const toggleFavorite = (id) => {
    setFavorites(prev => 
      prev.includes(id) ? prev.filter(fid => fid !== id) : [...prev, id]
    );
  };

  // 지도 중심 업데이트 컴포넌트
  const MapCenterUpdater = ({ center }) => {
    const map = useMap();
    const prevCenterRef = React.useRef(null);
    
    useEffect(() => {
      // 좌표가 실제로 변경되었을 때만 업데이트
      if (!prevCenterRef.current || 
          prevCenterRef.current[0] !== center[0] || 
          prevCenterRef.current[1] !== center[1]) {
        map.setView(center, map.getZoom());
        prevCenterRef.current = [...center]; // 배열 복사본 저장
      }
    }, [center[0], center[1], map]); // 좌표 값만 dependency로 사용
    
    return null;
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
              <p className="font-bold">{selectedLocation || '전체 지역'}</p>
            </div>
          </div>
          <div className="bg-white/20 backdrop-blur-lg rounded-xl p-4 flex items-center gap-3">
            <Clock className="text-white" size={24} />
            <div>
              <p className="text-sm opacity-80">진행중 공연</p>
              <p className="font-bold text-2xl">{filteredPerformances.length}개</p>
            </div>
          </div>
        </div>
      </div>

      {/* 지도 섹션 */}
      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-2xl font-bold text-white">실시간 공연 지도</h2>
          <div className="flex items-center gap-2 flex-wrap">
            <select
              value={selectedLocation}
              onChange={(e) => handleLocationSelect(e.target.value)}
              className="px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500"
            >
              <option value="">전체 지역</option>
              {Object.keys(locationCoordinates).map(location => (
                <option key={location} value={location}>{location}</option>
              ))}
            </select>
            {selectedLocation && (
              <button
                onClick={() => handleLocationSelect('')}
                className="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-gray-300 text-sm transition-colors"
              >
                초기화
              </button>
            )}
          </div>
        </div>
        <div className="rounded-xl overflow-hidden border border-gray-700 shadow-sm" style={{ height: '400px' }}>
          <MapContainer
            center={[userLocation.lat, userLocation.lng]}
            zoom={13}
            style={{ height: '100%', width: '100%' }}
            className="z-0"
          >
            <MapCenterUpdater center={useMemo(() => [userLocation.lat, userLocation.lng], [userLocation.lat, userLocation.lng])} />
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
                  <div className="text-white">
                    <div className="text-2xl mb-2">{perf.image}</div>
                    <h3 className="font-bold text-lg mb-1 text-white">{perf.buskerName}</h3>
                    <p className="text-xs text-gray-300 mb-1">
                      <MapPin size={12} className="inline mr-1" />
                      {perf.location}
                    </p>
                    <p className="text-xs text-gray-300 mb-1">
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
            className="bg-gray-800 rounded-2xl p-6 hover:bg-gray-750 transition-all cursor-pointer border border-gray-700 hover:border-purple-500 shadow-sm"
          >
            <div className="flex items-start justify-between mb-4">
              <div className="flex items-center gap-4">
                <div className="text-5xl">{perf.image}</div>
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="text-2xl font-bold text-white">{perf.buskerName}</h3>
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

      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <form className="space-y-6">
          {/* 기본 정보 */}
          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">팀/개인명 *</label>
            <input
              type="text"
              value={buskerForm.name}
              onChange={(e) => setBuskerForm({...buskerForm, name: e.target.value})}
              placeholder="예: 어쿠스틱 소울"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">팀 인원</label>
              <input
                type="number"
                min="1"
                value={buskerForm.teamSize}
                onChange={(e) => setBuskerForm({...buskerForm, teamSize: Number(e.target.value)})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
              />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">보유 장비</label>
            <input
              type="text"
              value={buskerForm.equipment}
              onChange={(e) => setBuskerForm({...buskerForm, equipment: e.target.value})}
              placeholder="예: 기타, 앰프, 마이크"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">연락처 *</label>
            <input
              type="tel"
              value={buskerForm.phone}
              onChange={(e) => setBuskerForm({...buskerForm, phone: e.target.value})}
              placeholder="010-0000-0000"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">소개</label>
            <textarea
              value={buskerForm.bio}
              onChange={(e) => setBuskerForm({...buskerForm, bio: e.target.value})}
              placeholder="팀 소개 및 공연 스타일을 자유롭게 작성해주세요"
              rows="4"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
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
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
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

  // 커뮤니티 페이지
  const [communityTab, setCommunityTab] = useState('free'); // 'free', 'recruit', 'collab'
  const [communityPosts, setCommunityPosts] = useState({
    free: [
      { id: 1, title: '천안역 버스킹 좋은 장소 추천해요!', author: '버스킹러버', date: '2024-01-15', views: 45, comments: 3 },
      { id: 2, title: '공연 장비 추천 부탁드립니다', author: '신입버스커', date: '2024-01-14', views: 32, comments: 5 },
      { id: 3, title: '버스킹 에티켓에 대해 알려주세요', author: '궁금이', date: '2024-01-13', views: 67, comments: 8 },
    ],
    recruit: [
      { id: 1, title: '기타리스트 구합니다 (어쿠스틱 팀)', author: '어쿠스틱소울', date: '2024-01-15', location: '천안', genre: '어쿠스틱' },
      { id: 2, title: '드러머 모집합니다', author: '록밴드', date: '2024-01-14', location: '천안', genre: '록' },
      { id: 3, title: '보컬 찾아요!', author: '재즈트리오', date: '2024-01-13', location: '천안', genre: '재즈' },
    ],
    collab: [
      { id: 1, title: '함께 공연할 팀 구합니다 (2월 공연)', author: '힙합크루', date: '2024-01-15', performanceDate: '2024-02-10', location: '천안역 광장' },
      { id: 2, title: '연합 공연 제안합니다', author: '어쿠스틱소울', date: '2024-01-14', performanceDate: '2024-02-15', location: '신세계 백화점 앞' },
    ]
  });

  const CommunityPage = () => (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl p-6 text-white">
        <h2 className="text-3xl font-bold mb-2">아티스트 커뮤니티</h2>
        <p>정보를 공유하고 함께 성장해요</p>
      </div>

      {/* 게시판 탭 */}
      <div className="flex gap-2 border-b border-gray-700">
        <button
          onClick={() => setCommunityTab('free')}
          className={`px-6 py-3 font-bold transition-colors border-b-2 ${
            communityTab === 'free' 
              ? 'border-purple-500 text-purple-400' 
              : 'border-transparent text-gray-400 hover:text-gray-300'
          }`}
        >
          자유게시판
        </button>
        <button
          onClick={() => setCommunityTab('recruit')}
          className={`px-6 py-3 font-bold transition-colors border-b-2 ${
            communityTab === 'recruit' 
              ? 'border-purple-500 text-purple-400' 
              : 'border-transparent text-gray-400 hover:text-gray-300'
          }`}
        >
          팀원모집
        </button>
        <button
          onClick={() => setCommunityTab('collab')}
          className={`px-6 py-3 font-bold transition-colors border-b-2 ${
            communityTab === 'collab' 
              ? 'border-purple-500 text-purple-400' 
              : 'border-transparent text-gray-400 hover:text-gray-300'
          }`}
        >
          함께공연
        </button>
      </div>

      {/* 게시글 목록 */}
      <div className="bg-gray-800 rounded-2xl border border-gray-700 shadow-sm">
        <div className="p-4 border-b border-gray-700 flex items-center justify-between">
          <h3 className="font-bold text-white">
            {communityTab === 'free' && '자유게시판'}
            {communityTab === 'recruit' && '팀원모집 게시판'}
            {communityTab === 'collab' && '함께공연 게시판'}
          </h3>
          <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold flex items-center gap-2">
            <Plus size={16} />
            글쓰기
          </button>
        </div>

        <div className="divide-y divide-gray-700">
          {communityTab === 'free' && communityPosts.free.map(post => (
            <div key={post.id} className="p-4 hover:bg-gray-750 cursor-pointer transition-colors">
              <h4 className="font-bold text-white mb-2">{post.title}</h4>
              <div className="flex items-center gap-4 text-sm text-gray-400">
                <span>{post.author}</span>
                <span>{post.date}</span>
                <span>조회 {post.views}</span>
                <span>댓글 {post.comments}</span>
              </div>
            </div>
          ))}
          {communityTab === 'recruit' && communityPosts.recruit.map(post => (
            <div key={post.id} className="p-4 hover:bg-gray-750 cursor-pointer transition-colors">
              <h4 className="font-bold text-white mb-2">{post.title}</h4>
              <div className="flex items-center gap-4 text-sm text-gray-400">
                <span>{post.author}</span>
                <span>{post.date}</span>
                <span className="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs border border-purple-700">{post.location}</span>
                <span className="px-2 py-1 bg-pink-900/50 text-pink-300 rounded text-xs border border-pink-700">{post.genre}</span>
              </div>
            </div>
          ))}
          {communityTab === 'collab' && communityPosts.collab.map(post => (
            <div key={post.id} className="p-4 hover:bg-gray-750 cursor-pointer transition-colors">
              <h4 className="font-bold text-white mb-2">{post.title}</h4>
              <div className="flex items-center gap-4 text-sm text-gray-400">
                <span>{post.author}</span>
                <span>{post.date}</span>
                <span className="px-2 py-1 bg-blue-900/50 text-blue-300 rounded text-xs border border-blue-700">공연일: {post.performanceDate}</span>
                <span className="px-2 py-1 bg-purple-900/50 text-purple-300 rounded text-xs border border-purple-700">{post.location}</span>
              </div>
            </div>
          ))}
        </div>
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

      <div className="bg-gray-800 rounded-2xl p-6 border border-gray-700 shadow-sm">
        <form className="space-y-6">
          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">주최자명 *</label>
            <input
              type="text"
              value={bookingForm.organizerName}
              onChange={(e) => setBookingForm({...bookingForm, organizerName: e.target.value})}
              placeholder="예: 천안시청, 백석대학교"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">주최자 유형 *</label>
            <select
              value={bookingForm.organizerType}
              onChange={(e) => setBookingForm({...bookingForm, organizerType: e.target.value})}
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
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
            <label className="block text-sm font-bold mb-2 text-gray-300">공연 장소 *</label>
            <input
              type="text"
              value={bookingForm.location}
              onChange={(e) => setBookingForm({...bookingForm, location: e.target.value})}
              placeholder="예: 천안역 광장"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
            />
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">공연 날짜 *</label>
            <input
              type="date"
              value={bookingForm.date}
              onChange={(e) => setBookingForm({...bookingForm, date: e.target.value})}
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
            />
          </div>

          <div className="grid md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-bold mb-2 text-gray-300">시작 시간 *</label>
              <input
                type="time"
                value={bookingForm.startTime}
                onChange={(e) => setBookingForm({...bookingForm, startTime: e.target.value})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
              />
            </div>

            <div>
              <label className="block text-sm font-bold mb-2 text-gray-300">종료 시간 *</label>
              <input
                type="time"
                value={bookingForm.endTime}
                onChange={(e) => setBookingForm({...bookingForm, endTime: e.target.value})}
                className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-bold mb-2 text-gray-300">추가 요청사항</label>
            <textarea
              value={bookingForm.additionalRequest}
              onChange={(e) => setBookingForm({...bookingForm, additionalRequest: e.target.value})}
              placeholder="특별한 요청사항이 있으시면 작성해주세요"
              rows="4"
              className="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-purple-500 text-white placeholder-gray-500"
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
    </div>
  );

  // 사용자 유형 선택 컴포넌트
  const UserTypeSelectModal = () => (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onClick={() => setShowUserTypeSelect(false)}>
      <div className="bg-white rounded-2xl max-w-2xl w-full p-8 shadow-xl" onClick={(e) => e.stopPropagation()}>
        <h2 className="text-3xl font-bold mb-6 text-gray-900 text-center">사용자 유형 선택</h2>
        <div className="grid grid-cols-2 gap-4">
          <button
            onClick={() => { setUserType('viewer'); setShowUserTypeSelect(false); }}
            className="p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all text-left"
          >
            <User className="text-purple-600 mb-3" size={32} />
            <h3 className="font-bold text-lg mb-2 text-gray-900">관람자</h3>
            <p className="text-sm text-gray-600">일반 시민</p>
          </button>
          <button
            onClick={() => { setUserType('artist'); setShowUserTypeSelect(false); }}
            className="p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all text-left"
          >
            <Music className="text-purple-600 mb-3" size={32} />
            <h3 className="font-bold text-lg mb-2 text-gray-900">아티스트</h3>
            <p className="text-sm text-gray-600">버스커</p>
          </button>
          <button
            onClick={() => { setUserType('business'); setShowUserTypeSelect(false); }}
            className="p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all text-left"
          >
            <Store className="text-purple-600 mb-3" size={32} />
            <h3 className="font-bold text-lg mb-2 text-gray-900">상업공간</h3>
            <p className="text-sm text-gray-600">카페, 라이브바 등</p>
          </button>
          <button
            onClick={() => { setUserType('organization'); setShowUserTypeSelect(false); }}
            className="p-6 border-2 border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition-all text-left"
          >
            <Building2 className="text-purple-600 mb-3" size={32} />
            <h3 className="font-bold text-lg mb-2 text-gray-900">기관(단체)</h3>
            <p className="text-sm text-gray-600">지자체, 공공기관</p>
          </button>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-b from-gray-900 via-slate-900 to-gray-900 text-white">
      {/* 헤더 */}
      <header className="sticky top-0 z-40 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800 shadow-sm">
        <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Music className="text-purple-500" size={32} />
            <h1 className="text-2xl font-bold text-white">버스킹고</h1>
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
            {(userType === 'artist' || !userType) && (
              <button
                onClick={() => setCurrentPage('register')}
                className={`font-bold transition-colors ${
                  currentPage === 'register' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
                }`}
              >
                버스커 등록
              </button>
            )}
            {(userType === 'business' || userType === 'organization' || !userType) && (
              <button
                onClick={() => setCurrentPage('booking')}
                className={`font-bold transition-colors ${
                  currentPage === 'booking' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
                }`}
              >
                공연 예약
              </button>
            )}
            {userType === 'artist' && (
              <>
                <button
                  onClick={() => setCurrentPage('alarm')}
              className={`font-bold transition-colors flex items-center gap-1 ${
                currentPage === 'alarm' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
                >
                  <Bell size={18} />
                  맞춤 알람
                </button>
                <button
                  onClick={() => setCurrentPage('community')}
              className={`font-bold transition-colors flex items-center gap-1 ${
                currentPage === 'community' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
                >
                  <MessageSquare size={18} />
                  커뮤니티
                </button>
              </>
            )}
            {userType === 'organization' && (
              <button
                onClick={() => setCurrentPage('contest')}
              className={`font-bold transition-colors flex items-center gap-1 ${
                currentPage === 'contest' ? 'text-purple-400' : 'text-gray-400 hover:text-white'
              }`}
              >
                <FileText size={18} />
                온라인 공모
              </button>
            )}
          </nav>

          {/* 사용자 유형 표시 및 변경 */}
          <div className="flex items-center gap-4">
            {userType ? (
              <div className="flex items-center gap-2 px-4 py-2 bg-purple-900/50 rounded-lg border border-purple-700">
                <span className="text-sm font-bold text-purple-300">
                  {userType === 'viewer' && '👀 관람자'}
                  {userType === 'artist' && '🎤 아티스트'}
                  {userType === 'business' && '🏪 상업공간'}
                  {userType === 'organization' && '🏛️ 기관(단체)'}
                </span>
                <button
                  onClick={() => setShowUserTypeSelect(true)}
                  className="text-xs text-purple-400 hover:text-purple-300"
                >
                  변경
                </button>
              </div>
            ) : (
              <button
                onClick={() => setShowUserTypeSelect(true)}
                className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-bold"
              >
                로그인
              </button>
            )}
            {/* 모바일 메뉴 버튼 */}
            <button 
              className="md:hidden p-2 hover:bg-gray-800 rounded-lg text-gray-300"
              onClick={() => setIsMenuOpen(!isMenuOpen)}
            >
              <Menu size={24} />
            </button>
          </div>
        </div>

        {/* 모바일 메뉴 */}
        {isMenuOpen && (
          <div className="md:hidden bg-gray-800 border-t border-gray-700 shadow-lg">
            <nav className="flex flex-col">
              <button
                onClick={() => { setCurrentPage('home'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors text-gray-300"
              >
                공연 찾기
              </button>
              <button
                onClick={() => { setCurrentPage('register'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors text-gray-300"
              >
                버스커 등록
              </button>
              <button
                onClick={() => { setCurrentPage('booking'); setIsMenuOpen(false); }}
                className="px-4 py-3 text-left hover:bg-gray-700 transition-colors text-gray-300"
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
        {currentPage === 'community' && <CommunityPage />}
      </main>

      {/* 사용자 유형 선택 모달 */}
      {showUserTypeSelect && (
        <div className="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onClick={() => setShowUserTypeSelect(false)}>
          <div className="bg-gray-800 rounded-2xl max-w-2xl w-full p-8 shadow-xl border border-gray-700" onClick={(e) => e.stopPropagation()}>
            <h2 className="text-3xl font-bold mb-6 text-white text-center">사용자 유형 선택</h2>
            <div className="grid grid-cols-2 gap-4">
              <button
                onClick={() => { setUserType('viewer'); setShowUserTypeSelect(false); }}
                className="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left"
              >
                <User className="text-purple-400 mb-3" size={32} />
                <h3 className="font-bold text-lg mb-2 text-white">관람자</h3>
                <p className="text-sm text-gray-400">일반 시민</p>
              </button>
              <button
                onClick={() => { setUserType('artist'); setShowUserTypeSelect(false); }}
                className="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left"
              >
                <Music className="text-purple-400 mb-3" size={32} />
                <h3 className="font-bold text-lg mb-2 text-white">아티스트</h3>
                <p className="text-sm text-gray-400">버스커</p>
              </button>
              <button
                onClick={() => { setUserType('business'); setShowUserTypeSelect(false); }}
                className="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left"
              >
                <Store className="text-purple-400 mb-3" size={32} />
                <h3 className="font-bold text-lg mb-2 text-white">상업공간</h3>
                <p className="text-sm text-gray-400">카페, 라이브바 등</p>
              </button>
              <button
                onClick={() => { setUserType('organization'); setShowUserTypeSelect(false); }}
                className="p-6 border-2 border-gray-700 rounded-xl hover:border-purple-500 hover:bg-purple-900/20 transition-all text-left"
              >
                <Building2 className="text-purple-400 mb-3" size={32} />
                <h3 className="font-bold text-lg mb-2 text-white">기관(단체)</h3>
                <p className="text-sm text-gray-400">지자체, 공공기관</p>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* 공연 상세 모달 */}
      {selectedPerformance && (
        <div className="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" onClick={() => setSelectedPerformance(null)}>
          <div className="bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-700 shadow-xl" onClick={(e) => e.stopPropagation()}>
            <div className="p-6">
              <div className="flex items-start justify-between mb-6">
                <div className="flex items-center gap-4">
                  <div className="text-6xl">{selectedPerformance.image}</div>
                  <div>
                    <h2 className="text-3xl font-bold mb-2 text-white">{selectedPerformance.buskerName}</h2>
                  </div>
                </div>
                <button onClick={() => setSelectedPerformance(null)} className="p-2 hover:bg-gray-700 rounded-full text-gray-400">
                  <X size={24} />
                </button>
              </div>

              <div className="space-y-6">
                <div className="bg-gray-900 rounded-xl p-4 border border-gray-700">
                  <h3 className="font-bold mb-3 text-white">공연 정보</h3>
                  <div className="space-y-2">
                    <div className="flex items-center gap-2 text-gray-300">
                      <MapPin size={18} className="text-purple-400" />
                      <span>{selectedPerformance.location}</span>
                    </div>
                    <div className="flex items-center gap-2 text-gray-300">
                      <Clock size={18} className="text-purple-400" />
                      <span>{selectedPerformance.startTime} - {selectedPerformance.endTime}</span>
                    </div>
                    <div className="flex items-center gap-2 text-gray-300">
                      <Navigation size={18} className="text-purple-400" />
                      <span>현재 위치에서 {selectedPerformance.distance}km</span>
                    </div>
                    <div className="flex items-center gap-2 text-yellow-400">
                      <Star size={18} className="text-yellow-400" fill="currentColor" />
                      <span>{selectedPerformance.rating} / 5.0</span>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 className="font-bold mb-3 text-white">공연 소개</h3>
                  <p className="text-gray-300">{selectedPerformance.description}</p>
                </div>

                {/* QR 팁박스 섹션 */}
                <div className="bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-xl p-4 border border-purple-700">
                  <div className="flex items-center gap-3 mb-3">
                    <QrCode className="text-purple-400" size={24} />
                    <h3 className="font-bold text-white">QR 모바일 팁박스</h3>
                  </div>
                  <p className="text-sm text-gray-300 mb-4">QR 코드를 스캔하여 아티스트에게 팁을 후원하세요</p>
                  <div className="flex gap-2">
                    <button className="flex-1 bg-purple-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                      QR 스캔하기
                    </button>
                    <button className="px-4 py-2 bg-gray-700 border border-purple-500 text-purple-300 rounded-lg hover:bg-gray-600 transition-colors text-sm font-bold">
                      팁 후원하기
                    </button>
                  </div>
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
