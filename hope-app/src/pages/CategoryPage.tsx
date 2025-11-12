import React from 'react';
import { Header } from '../components/Header';
import { VerseCard } from '../components/VerseCard';
import { categories } from '../data/categories';
import { getVersesByCategory } from '../data/verses';

interface CategoryPageProps {
  categoryId: string;
  onBack: () => void;
}

export const CategoryPage: React.FC<CategoryPageProps> = ({ categoryId, onBack }) => {
  const category = categories.find(c => c.id === categoryId);
  const verses = getVersesByCategory(categoryId);

  if (!category) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-gray-600">카테고리를 찾을 수 없습니다.</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-hope-calm to-hope-warm">
      <Header onBack={onBack} title={category.name} />

      <main className="max-w-4xl mx-auto px-4 py-8">
        {/* 카테고리 헤더 */}
        <div
          className="card mb-8 text-center"
          style={{ borderTop: `4px solid ${category.color}` }}
        >
          <div className="text-6xl mb-4">{category.icon}</div>
          <h2 className="text-3xl font-bold text-gray-800 mb-3">
            {category.name}
          </h2>
          <p className="text-gray-600 text-lg mb-4">
            {category.description}
          </p>
          <div className="flex flex-wrap gap-2 justify-center">
            {category.keywords.map((keyword, index) => (
              <span
                key={index}
                className="px-3 py-1 text-sm rounded-full text-white"
                style={{ backgroundColor: category.color }}
              >
                {keyword}
              </span>
            ))}
          </div>
        </div>

        {/* 말씀 목록 */}
        <section>
          <h3 className="text-xl font-bold text-gray-800 mb-4">
            당신을 위한 말씀 ({verses.length}개)
          </h3>
          <div className="space-y-6">
            {verses.map((verse) => (
              <VerseCard key={verse.id} verse={verse} showCategory />
            ))}
          </div>
        </section>

        {verses.length === 0 && (
          <div className="text-center py-12">
            <p className="text-gray-600 text-lg">
              이 카테고리의 말씀을 준비 중입니다.
            </p>
          </div>
        )}
      </main>
    </div>
  );
};
