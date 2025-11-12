import { Category } from '../types';

export const categories: Category[] = [
  {
    id: 'stress',
    name: '스트레스와 압박',
    description: '일과 가정의 균형, 과중한 업무로 힘들 때',
    icon: '💪',
    color: '#FF6B6B',
    keywords: ['스트레스', '압박', '피곤', '지침', '번아웃']
  },
  {
    id: 'parenting',
    name: '육아의 어려움',
    description: '아이 양육과 교육으로 고민될 때',
    icon: '👨‍👩‍👧‍👦',
    color: '#4ECDC4',
    keywords: ['육아', '자녀', '교육', '양육', '아이']
  },
  {
    id: 'anxiety',
    name: '불안과 걱정',
    description: '미래가 불안하고 걱정이 많을 때',
    icon: '🕊️',
    color: '#95E1D3',
    keywords: ['불안', '걱정', '두려움', '염려', '근심']
  },
  {
    id: 'depression',
    name: '우울과 무기력',
    description: '의욕이 없고 우울한 감정이 들 때',
    icon: '🌈',
    color: '#FFE66D',
    keywords: ['우울', '무기력', '슬픔', '외로움', '고독']
  },
  {
    id: 'financial',
    name: '재정적 어려움',
    description: '경제적 부담과 재정 문제로 힘들 때',
    icon: '💰',
    color: '#A8E6CF',
    keywords: ['재정', '돈', '경제', '빚', '생계']
  },
  {
    id: 'relationship',
    name: '관계의 갈등',
    description: '부부, 가족, 직장 관계에서 어려움이 있을 때',
    icon: '❤️',
    color: '#FFB6D9',
    keywords: ['관계', '갈등', '부부', '가족', '대인관계']
  },
  {
    id: 'health',
    name: '건강 문제',
    description: '몸과 마음의 건강이 염려될 때',
    icon: '🏥',
    color: '#C7CEEA',
    keywords: ['건강', '질병', '아픔', '치유', '회복']
  },
  {
    id: 'purpose',
    name: '삶의 의미',
    description: '인생의 방향과 목적을 찾고 싶을 때',
    icon: '🎯',
    color: '#FFDFD3',
    keywords: ['의미', '목적', '방향', '소명', '정체성']
  },
  {
    id: 'gratitude',
    name: '감사와 희망',
    description: '감사하는 마음과 희망이 필요할 때',
    icon: '🙏',
    color: '#B4E7CE',
    keywords: ['감사', '희망', '기쁨', '평안', '축복']
  },
  {
    id: 'wisdom',
    name: '지혜와 결단',
    description: '중요한 결정과 지혜가 필요할 때',
    icon: '💡',
    color: '#F7D794',
    keywords: ['지혜', '결단', '선택', '판단', '결정']
  }
];
