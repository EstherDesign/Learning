export interface Verse {
  id: string;
  book: string;
  chapter: number;
  verse: number;
  text: string;
  reference: string;
}

export interface Category {
  id: string;
  name: string;
  description: string;
  icon: string;
  color: string;
  keywords: string[];
}

export interface VerseWithCategory extends Verse {
  categories: string[];
  tags: string[];
  imageUrl?: string;
  videoUrl?: string;
}

export interface DailyVerse extends VerseWithCategory {
  date: string;
  reflection: string;
}
