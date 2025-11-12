import React from 'react';
import { Header } from '../components/Header';
import { CategoryCard } from '../components/CategoryCard';
import { VerseCard } from '../components/VerseCard';
import { categories } from '../data/categories';
import { getTodayVerse } from '../data/verses';

interface HomeProps {
  onCategoryClick: (categoryId: string) => void;
}

export const Home: React.FC<HomeProps> = ({ onCategoryClick }) => {
  const todayVerse = getTodayVerse();

  return (
    <div className="min-h-screen bg-gradient-to-br from-hope-calm to-hope-warm">
      <Header />

      <main className="max-w-4xl mx-auto px-4 py-8">
        {/* 환영 섹션 */}
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold text-gray-800 mb-4">
            오늘도 희망을 품고 🌟
          </h2>
          <p className="text-lg text-gray-600 leading-relaxed">
            현대를 살아가는 당신에게<br />
            성경 말씀을 통한 위로와 힘을 전합니다
          </p>
        </div>

        {/* 오늘의 말씀 */}
        <section className="mb-12">
          <h3 className="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <span>📖</span>
            <span>오늘의 말씀</span>
          </h3>
          <VerseCard verse={todayVerse} showCategory />
        </section>

        {/* 상황별 카테고리 */}
        <section>
          <h3 className="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span>💭</span>
            <span>지금 당신의 상황은?</span>
          </h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {categories.map((category) => (
              <CategoryCard
                key={category.id}
                category={category}
                onClick={() => onCategoryClick(category.id)}
              />
            ))}
          </div>
        </section>

        {/* 푸터 */}
        <footer className="mt-16 text-center text-gray-500 text-sm pb-8">
          <p className="mb-2">
            모든 말씀은 성경(개역개정판)에서 발췌했습니다
          </p>
          <p className="text-xs">
            Hope - 현대인을 위한 성경 위로 서비스
          </p>
        </footer>
      </main>
    </div>
  );
};
