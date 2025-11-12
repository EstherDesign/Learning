import { VerseWithCategory } from '../types';

export const verses: VerseWithCategory[] = [
  // 스트레스와 압박
  {
    id: 'v1',
    book: '마태복음',
    chapter: 11,
    verse: 28,
    text: '수고하고 무거운 짐 진 자들아 다 내게로 오라 내가 너희를 쉬게 하리라',
    reference: '마태복음 11:28',
    categories: ['stress'],
    tags: ['쉼', '위로', '초대']
  },
  {
    id: 'v2',
    book: '빌립보서',
    chapter: 4,
    verse: 6,
    text: '아무 것도 염려하지 말고 다만 모든 일에 기도와 간구로, 너희 구할 것을 감사함으로 하나님께 아뢰라',
    reference: '빌립보서 4:6',
    categories: ['stress', 'anxiety'],
    tags: ['염려', '기도', '평안']
  },
  {
    id: 'v3',
    book: '시편',
    chapter: 55,
    verse: 22,
    text: '네 짐을 여호와께 맡기라 그가 너를 붙드시고 의인의 요동함을 영원히 허락하지 아니하시리로다',
    reference: '시편 55:22',
    categories: ['stress'],
    tags: ['짐', '맡김', '붙드심']
  },

  // 육아의 어려움
  {
    id: 'v4',
    book: '잠언',
    chapter: 22,
    verse: 6,
    text: '마땅히 행할 길을 아이에게 가르치라 그리하면 늙어도 그것을 떠나지 아니하리라',
    reference: '잠언 22:6',
    categories: ['parenting'],
    tags: ['교육', '양육', '가르침']
  },
  {
    id: 'v5',
    book: '시편',
    chapter: 127,
    verse: 3,
    text: '자식은 여호와의 기업이요 태의 열매는 그의 상급이로다',
    reference: '시편 127:3',
    categories: ['parenting', 'gratitude'],
    tags: ['자녀', '축복', '기업']
  },
  {
    id: 'v6',
    book: '에베소서',
    chapter: 6,
    verse: 4,
    text: '또 아비들아 너희 자녀를 노엽게 하지 말고 오직 주의 교훈과 훈계로 양육하라',
    reference: '에베소서 6:4',
    categories: ['parenting'],
    tags: ['양육', '교훈', '훈계']
  },

  // 불안과 걱정
  {
    id: 'v7',
    book: '마태복음',
    chapter: 6,
    verse: 34,
    text: '그러므로 내일 일을 위하여 염려하지 말라 내일 일은 내일이 염려할 것이요 한 날의 괴로움은 그 날로 족하니라',
    reference: '마태복음 6:34',
    categories: ['anxiety', 'stress'],
    tags: ['염려', '오늘', '내일']
  },
  {
    id: 'v8',
    book: '이사야',
    chapter: 41,
    verse: 10,
    text: '두려워하지 말라 내가 너와 함께 함이라 놀라지 말라 나는 네 하나님이 됨이라 내가 너를 굳세게 하리라 참으로 너를 도와주리라 참으로 나의 의로운 오른손으로 너를 붙들리라',
    reference: '이사야 41:10',
    categories: ['anxiety', 'stress'],
    tags: ['두려움', '함께', '붙드심']
  },
  {
    id: 'v9',
    book: '빌립보서',
    chapter: 4,
    verse: 7,
    text: '그리하면 모든 지각에 뛰어난 하나님의 평강이 그리스도 예수 안에서 너희 마음과 생각을 지키시리라',
    reference: '빌립보서 4:7',
    categories: ['anxiety'],
    tags: ['평강', '평안', '지키심']
  },

  // 우울과 무기력
  {
    id: 'v10',
    book: '시편',
    chapter: 42,
    verse: 11,
    text: '내 영혼아 네가 어찌하여 낙심하며 어찌하여 내 속에서 불안해 하는가 너는 하나님께 소망을 두라 나는 그가 나타나 도우심으로 말미암아 내 하나님을 여전히 찬송하리로다',
    reference: '시편 42:11',
    categories: ['depression'],
    tags: ['낙심', '소망', '찬송']
  },
  {
    id: 'v11',
    book: '이사야',
    chapter: 40,
    verse: 31,
    text: '오직 여호와를 앙망하는 자는 새 힘을 얻으리니 독수리가 날개치며 올라감 같을 것이요 달음박질하여도 곤비하지 아니하겠고 걸어가도 피곤하지 아니하리로다',
    reference: '이사야 40:31',
    categories: ['depression', 'stress'],
    tags: ['힘', '회복', '앙망']
  },
  {
    id: 'v12',
    book: '시편',
    chapter: 34,
    verse: 18,
    text: '여호와는 마음이 상한 자를 가까이하시고 충심으로 통회하는 자를 구원하시는도다',
    reference: '시편 34:18',
    categories: ['depression'],
    tags: ['위로', '구원', '가까이']
  },

  // 재정적 어려움
  {
    id: 'v13',
    book: '빌립보서',
    chapter: 4,
    verse: 19,
    text: '나의 하나님이 그리스도 예수 안에서 영광 가운데 그 풍성한 대로 너희 모든 쓸 것을 채우시리라',
    reference: '빌립보서 4:19',
    categories: ['financial'],
    tags: ['공급', '채우심', '풍성']
  },
  {
    id: 'v14',
    book: '마태복음',
    chapter: 6,
    verse: 33,
    text: '그런즉 너희는 먼저 그의 나라와 그의 의를 구하라 그리하면 이 모든 것을 너희에게 더하시리라',
    reference: '마태복음 6:33',
    categories: ['financial', 'purpose'],
    tags: ['우선순위', '공급', '하나님나라']
  },
  {
    id: 'v15',
    book: '잠언',
    chapter: 3,
    verse: 9,
    text: '네 재물과 네 소산물의 처음 익은 열매로 여호와를 공경하라',
    reference: '잠언 3:9-10',
    categories: ['financial', 'gratitude'],
    tags: ['재물', '공경', '축복']
  },

  // 관계의 갈등
  {
    id: 'v16',
    book: '에베소서',
    chapter: 4,
    verse: 32,
    text: '서로 친절하게 하며 불쌍히 여기며 서로 용서하기를 하나님이 그리스도 안에서 너희를 용서하심과 같이 하라',
    reference: '에베소서 4:32',
    categories: ['relationship'],
    tags: ['용서', '친절', '화해']
  },
  {
    id: 'v17',
    book: '골로새서',
    chapter: 3,
    verse: 13,
    text: '누가 누구에게 불만이 있거든 서로 용납하여 피차 용서하되 주께서 너희를 용서하신 것 같이 너희도 그리하고',
    reference: '골로새서 3:13',
    categories: ['relationship'],
    tags: ['용납', '용서', '화목']
  },
  {
    id: 'v18',
    book: '베드로전서',
    chapter: 4,
    verse: 8,
    text: '무엇보다도 뜨겁게 서로 사랑할지니 사랑은 허다한 죄를 덮느니라',
    reference: '베드로전서 4:8',
    categories: ['relationship'],
    tags: ['사랑', '용서', '덮음']
  },

  // 건강 문제
  {
    id: 'v19',
    book: '시편',
    chapter: 103,
    verse: 3,
    text: '그가 네 모든 죄악을 사하시며 네 모든 병을 고치시며',
    reference: '시편 103:3',
    categories: ['health'],
    tags: ['치유', '회복', '고치심']
  },
  {
    id: 'v20',
    book: '예레미야',
    chapter: 30,
    verse: 17,
    text: '여호와께서 이르시되 내가 너를 치료하여 네 상처를 낫게 하리라',
    reference: '예레미야 30:17',
    categories: ['health'],
    tags: ['치료', '상처', '회복']
  },
  {
    id: 'v21',
    book: '요한3서',
    chapter: 1,
    verse: 2,
    text: '사랑하는 자여 네 영혼이 잘됨 같이 네가 범사에 잘되고 강건하기를 내가 간구하노라',
    reference: '요한3서 1:2',
    categories: ['health', 'gratitude'],
    tags: ['강건', '축복', '잘됨']
  },

  // 삶의 의미
  {
    id: 'v22',
    book: '예레미야',
    chapter: 29,
    verse: 11,
    text: '여호와의 말씀이니라 너희를 향한 나의 생각을 내가 아나니 평안이요 재앙이 아니니라 너희에게 미래와 희망을 주는 것이니라',
    reference: '예레미야 29:11',
    categories: ['purpose', 'anxiety'],
    tags: ['계획', '희망', '미래']
  },
  {
    id: 'v23',
    book: '잠언',
    chapter: 3,
    verse: 5,
    text: '너는 마음을 다하여 여호와를 신뢰하고 네 명철을 의지하지 말라',
    reference: '잠언 3:5-6',
    categories: ['purpose', 'wisdom'],
    tags: ['신뢰', '인도', '의지']
  },
  {
    id: 'v24',
    book: '에베소서',
    chapter: 2,
    verse: 10,
    text: '우리는 그가 만드신 바라 그리스도 예수 안에서 선한 일을 위하여 지으심을 받은 자니',
    reference: '에베소서 2:10',
    categories: ['purpose'],
    tags: ['창조', '목적', '선한일']
  },

  // 감사와 희망
  {
    id: 'v25',
    book: '데살로니가전서',
    chapter: 5,
    verse: 18,
    text: '범사에 감사하라 이것이 그리스도 예수 안에서 너희를 향하신 하나님의 뜻이니라',
    reference: '데살로니가전서 5:18',
    categories: ['gratitude'],
    tags: ['감사', '범사', '하나님의뜻']
  },
  {
    id: 'v26',
    book: '시편',
    chapter: 100,
    verse: 4,
    text: '감사함으로 그의 문에 들어가며 찬송함으로 그의 궁정에 들어가서 그에게 감사하며 그의 이름을 송축할지어다',
    reference: '시편 100:4',
    categories: ['gratitude'],
    tags: ['감사', '찬송', '기쁨']
  },
  {
    id: 'v27',
    book: '로마서',
    chapter: 15,
    verse: 13,
    text: '소망의 하나님이 모든 기쁨과 평강을 믿음 안에서 너희에게 충만하게 하사 성령의 능력으로 소망이 넘치게 하시기를 원하노라',
    reference: '로마서 15:13',
    categories: ['gratitude', 'anxiety'],
    tags: ['소망', '기쁨', '평강']
  },

  // 지혜와 결단
  {
    id: 'v28',
    book: '야고보서',
    chapter: 1,
    verse: 5,
    text: '너희 중에 누구든지 지혜가 부족하거든 모든 사람에게 후히 주시고 꾸짖지 아니하시는 하나님께 구하라 그리하면 주시리라',
    reference: '야고보서 1:5',
    categories: ['wisdom'],
    tags: ['지혜', '구함', '주심']
  },
  {
    id: 'v29',
    book: '잠언',
    chapter: 16,
    verse: 3,
    text: '네 행사를 여호와께 맡기라 그리하면 네가 경영하는 것이 이루어지리라',
    reference: '잠언 16:3',
    categories: ['wisdom', 'purpose'],
    tags: ['맡김', '경영', '성취']
  },
  {
    id: 'v30',
    book: '시편',
    chapter: 32,
    verse: 8,
    text: '내가 네게 가르쳐 보일 것이며 네가 갈 길을 가르쳐 보일 것이며 내가 너를 주목하여 훈계하리로다',
    reference: '시편 32:8',
    categories: ['wisdom', 'purpose'],
    tags: ['인도', '가르침', '길']
  }
];

// 카테고리별로 말씀 찾기
export const getVersesByCategory = (categoryId: string): VerseWithCategory[] => {
  return verses.filter(verse => verse.categories.includes(categoryId));
};

// 랜덤 말씀 가져오기
export const getRandomVerse = (): VerseWithCategory => {
  return verses[Math.floor(Math.random() * verses.length)];
};

// 오늘의 말씀 (날짜 기반)
export const getTodayVerse = (): VerseWithCategory => {
  const today = new Date();
  const dayOfYear = Math.floor((today.getTime() - new Date(today.getFullYear(), 0, 0).getTime()) / 86400000);
  return verses[dayOfYear % verses.length];
};
