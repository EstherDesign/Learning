import { useState } from 'react';
import { Home } from './pages/Home';
import { CategoryPage } from './pages/CategoryPage';
import './index.css';

type Page =
  | { type: 'home' }
  | { type: 'category'; categoryId: string };

function App() {
  const [currentPage, setCurrentPage] = useState<Page>({ type: 'home' });

  const handleCategoryClick = (categoryId: string) => {
    setCurrentPage({ type: 'category', categoryId });
  };

  const handleBack = () => {
    setCurrentPage({ type: 'home' });
  };

  return (
    <>
      {currentPage.type === 'home' && (
        <Home onCategoryClick={handleCategoryClick} />
      )}
      {currentPage.type === 'category' && (
        <CategoryPage
          categoryId={currentPage.categoryId}
          onBack={handleBack}
        />
      )}
    </>
  );
}

export default App;
