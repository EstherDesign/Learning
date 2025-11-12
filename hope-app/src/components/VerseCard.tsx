import React from 'react';
import { VerseWithCategory } from '../types';

interface VerseCardProps {
  verse: VerseWithCategory;
  showCategory?: boolean;
}

export const VerseCard: React.FC<VerseCardProps> = ({ verse, showCategory = false }) => {
  return (
    <div className="card">
      <div className="mb-4">
        <p className="verse-text mb-6 text-center italic">
          "{verse.text}"
        </p>
        <p className="text-right text-hope-primary font-semibold text-base">
          - {verse.reference}
        </p>
      </div>

      {showCategory && verse.categories.length > 0 && (
        <div className="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
          {verse.tags.map((tag, index) => (
            <span
              key={index}
              className="px-3 py-1 bg-hope-calm text-hope-primary text-xs rounded-full"
            >
              #{tag}
            </span>
          ))}
        </div>
      )}
    </div>
  );
};
