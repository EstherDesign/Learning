import React from 'react';
import { Category } from '../types';

interface CategoryCardProps {
  category: Category;
  onClick: () => void;
}

export const CategoryCard: React.FC<CategoryCardProps> = ({ category, onClick }) => {
  return (
    <div
      onClick={onClick}
      className="card cursor-pointer transform hover:scale-105 transition-transform"
      style={{ borderTop: `4px solid ${category.color}` }}
    >
      <div className="flex items-center gap-4">
        <div
          className="text-5xl flex-shrink-0 w-16 h-16 flex items-center justify-center rounded-full"
          style={{ backgroundColor: `${category.color}20` }}
        >
          {category.icon}
        </div>
        <div className="flex-1">
          <h3 className="text-xl font-bold text-gray-800 mb-1">
            {category.name}
          </h3>
          <p className="text-sm text-gray-600 leading-relaxed">
            {category.description}
          </p>
        </div>
      </div>
    </div>
  );
};
