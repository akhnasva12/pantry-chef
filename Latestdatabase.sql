-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2026 at 09:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pantry_chef`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('meal','food_type','special') DEFAULT 'meal',
  `icon` varchar(10) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`, `icon`, `sort_order`) VALUES
(1, 'Breakfast', 'meal', '🌅', 1),
(2, 'Lunch', 'meal', '☀️', 2),
(3, 'Dinner', 'meal', '🌙', 3),
(4, 'Snacks', 'meal', '🍿', 4),
(5, 'Fast Food', 'meal', '🍔', 5),
(6, 'Street Food', 'meal', '🌮', 6),
(7, 'Bakery', 'meal', '🥐', 7),
(8, 'Desserts', 'meal', '🍰', 8),
(9, 'Sweets', 'meal', '🍬', 9),
(10, 'Beverages', 'meal', '🥤', 10),
(11, 'Juices', 'meal', '🍊', 11),
(12, 'Smoothies', 'meal', '🥤', 12),
(13, 'Tea & Coffee', 'meal', '☕', 13),
(14, 'Soups', 'meal', '🍜', 14),
(15, 'Salads', 'meal', '🥗', 15),
(16, 'Veg', 'food_type', '🥦', 16),
(17, 'Non-Veg', 'food_type', '🍗', 17),
(18, 'Vegan', 'food_type', '🌱', 18),
(19, 'Egg-based', 'food_type', '🥚', 19),
(20, 'Healthy', 'special', '💪', 20),
(21, 'Quick Meals', 'special', '⚡', 21),
(22, 'High Protein', 'special', '🏋️', 22),
(23, 'Low Budget', 'special', '💰', 23),
(24, 'Kids Friendly', 'special', '👶', 24),
(25, 'Spicy', 'special', '🌶️', 25),
(26, 'Ramadan Special', 'special', '🌙', 26);

-- --------------------------------------------------------

--
-- Table structure for table `creators`
--

CREATE TABLE `creators` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `chef_name` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `speciality_cuisine` varchar(100) DEFAULT NULL,
  `social_link` varchar(255) DEFAULT NULL,
  `experience_years` tinyint(3) UNSIGNED DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creators`
--

INSERT INTO `creators` (`id`, `user_id`, `chef_name`, `bio`, `profile_image`, `location`, `speciality_cuisine`, `social_link`, `experience_years`, `status`) VALUES
(2, 4, 'fthmthh', 'authentic japanese food', NULL, 'tokyo,japan', 'Japanese', 'nashreeenn', 5, 'pending'),
(4, 7, 'lumeofsana', 'from masalas to memories ✨\r\nindian kitchen diaries\r\nfood made with feeling, not just recipes', NULL, 'kasaragod kerala', 'Indian', 'https://www.youtube.com/', 3, 'approved'),
(5, 8, 'shanzorae', 'delicious thai food', NULL, 'bangkok,thaiand', 'Thai', 'shanzorae', 2, 'pending'),
(6, 12, 'chef', 'goood chef', 'chef_69ecc955b1efe2.77677543.jpg', 'mangluru', 'Indian', '', 3, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `recipe_id`, `created_at`) VALUES
(2, 8, 4, '2026-04-15 18:26:32'),
(4, 7, 7, '2026-04-21 10:15:27'),
(5, 7, 6, '2026-04-21 10:15:34'),
(6, 10, 7, '2026-04-21 10:17:13'),
(7, 10, 6, '2026-04-21 10:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id`, `user_id`, `recipe_id`, `viewed_at`) VALUES
(2, 5, 5, '2026-04-15 18:29:39'),
(3, 9, 4, '2026-04-18 09:17:00'),
(4, 7, 7, '2026-04-18 10:04:37'),
(5, 7, 7, '2026-04-18 10:35:16'),
(6, 7, 3, '2026-04-18 10:36:18'),
(7, 10, 7, '2026-04-25 09:59:18'),
(8, 11, 7, '2026-04-25 12:07:44'),
(9, 11, 7, '2026-04-25 13:14:15');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `ingredient_name` varchar(200) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipe_id`, `ingredient_name`, `quantity`, `sort_order`) VALUES
(9, 2, '•	1 cup cooked rice', NULL, 1),
(10, 2, '•	Salt', NULL, 2),
(11, 2, '•	Filling (tuna / chicken / pickle', NULL, 3),
(12, 2, '•	Seaweed (optional)', NULL, 4),
(13, 3, '½ cup oats', NULL, 1),
(14, 3, '1 cup water (or milk if you want it creamy)', NULL, 2),
(15, 3, '½ cup veggies (carrot, peas, capsicum—whatever’s in the fridge)', NULL, 3),
(16, 3, '1 tsp oil or ghee', NULL, 4),
(17, 3, '½ tsp cumin seeds (jeera)', NULL, 5),
(18, 3, '½ tsp turmeric (haldi)', NULL, 6),
(19, 3, 'Salt (to taste)', NULL, 7),
(20, 3, 'Optional: green chili 🌶️, black pepper', NULL, 8),
(21, 4, '1 large roti or tortilla 🫓', NULL, 1),
(22, 4, '100–150g chicken (boneless) 🍗', NULL, 2),
(23, 4, '1 tsp oil', NULL, 3),
(24, 4, '½ onion (sliced) 🧅', NULL, 4),
(25, 4, '½ capsicum (optional) 🌶️', NULL, 5),
(26, 4, 'Salt', NULL, 6),
(27, 4, '½ tsp red chilli powder', NULL, 7),
(28, 4, '½ tsp turmeric', NULL, 8),
(29, 4, '½ tsp garam masala', NULL, 9),
(30, 4, 'Optional: curd or mayo + green chutney', NULL, 10),
(42, 6, '1 cup coconut milk 🥥', NULL, 1),
(43, 6, '1–2 tsp green curry paste (or homemade mix) 🌶️', NULL, 2),
(44, 6, '1 cup vegetables (beans, carrot, capsicum, broccoli) 🥕', NULL, 3),
(45, 6, '100–150g chicken / paneer / tofu (optional) 🍗🧀', NULL, 4),
(46, 6, '1 tsp oil', NULL, 5),
(47, 6, '1 tsp soy sauce (optional)', NULL, 6),
(48, 6, 'Salt to taste', NULL, 7),
(49, 6, 'Basil leaves (or coriander) 🌿', NULL, 8),
(61, 5, '100–150g paneer 🧀', NULL, 1),
(62, 5, '1 tsp oil or ghee', NULL, 2),
(63, 5, '½ onion 🧅', NULL, 3),
(64, 5, '½ tomato 🍅', NULL, 4),
(65, 5, '½ capsicum (optional) 🌶️', NULL, 5),
(66, 5, '½ green peas (optional)', NULL, 6),
(67, 5, '½ tsp turmeric', NULL, 7),
(68, 5, '1 tsp chilli powder', NULL, 8),
(69, 5, '½ tsp garam masala', NULL, 9),
(70, 5, 'Salt', NULL, 10),
(71, 5, 'Optional: coriander 🌿, lemon 🍋', NULL, 11),
(72, 7, 'Chicken – 1 kg (cut into medium pieces)', NULL, 1),
(73, 7, 'Yogurt – 1/2 cup', NULL, 2),
(74, 7, 'Ginger-garlic paste – 2 tbsp', NULL, 3),
(75, 7, 'Lemon juice – 2 tbsp', NULL, 4),
(76, 7, 'Red chili powder – 1 tbsp', NULL, 5),
(77, 7, 'Turmeric powder – 1/2 tsp', NULL, 6),
(78, 7, 'Coriander powder – 1 tbsp', NULL, 7),
(79, 7, 'Cumin powder – 1 tsp', NULL, 8),
(80, 7, 'Garam masala – 1 tsp', NULL, 9),
(81, 7, 'Salt – 1.5 tsp (adjust to taste)', NULL, 10),
(82, 7, 'Oil – 2 tbsp', NULL, 11),
(83, 7, 'Basmati rice – 3 cups', NULL, 12),
(84, 7, 'Water or chicken stock – 5 cups', NULL, 13),
(85, 7, 'Onion – 2 large (thinly sliced)', NULL, 14),
(86, 7, 'Tomato – 2 medium (chopped)', NULL, 15),
(87, 7, 'Green chilies – 2 (slit)', NULL, 16),
(88, 7, 'Bay leaves – 2', NULL, 17),
(89, 7, 'Cloves – 4', NULL, 18),
(90, 7, 'Cardamom – 4', NULL, 19),
(91, 7, 'Cinnamon stick – 1 small', NULL, 20),
(92, 7, 'Black peppercorns – 1 tsp', NULL, 21),
(93, 7, 'Cumin seeds – 1 tsp', NULL, 22),
(94, 7, 'Salt – to taste', NULL, 23),
(95, 7, 'Oil or ghee – 3 tbsp', NULL, 24),
(96, 7, 'Charcoal – 1 small piece', NULL, 25),
(97, 7, 'Oil or ghee – 1 tsp', NULL, 26);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cooking_time` smallint(5) UNSIGNED DEFAULT 0 COMMENT 'minutes',
  `budget` decimal(8,2) DEFAULT 0.00 COMMENT 'in local currency',
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `cuisine` varchar(100) DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `status` enum('active','draft','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `description`, `image`, `cooking_time`, `budget`, `category_id`, `cuisine`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(2, 'onigiri', 'A simple Japanese snack made with rice shaped into triangles and filled with savory ingredients.', 'recipe_69dfc5477ddc15.71229251.jpeg', 10, 100.00, 4, 'Japanese', 4, 'active', '2026-04-15 17:05:11', '2026-04-15 17:05:11'),
(3, 'Masala Oats', 'Masala Oats is a healthy and flavorful Indian breakfast made by cooking oats with mixed vegetables and simple spices. It has a soft, slightly creamy texture with a mildly spicy and savory taste. Ingredients like cumin, turmeric, and vegetables such as carrots, peas, and capsicum enhance both its nutrition and flavor. This dish is quick to prepare, light on the stomach, and rich in fiber, making it an ideal option for a balanced morning meal.', 'recipe_69dfd010d2b575.95112653.jpeg', 15, 100.00, 1, 'Indian', 7, 'active', '2026-04-15 17:51:12', '2026-04-15 17:51:12'),
(4, 'Chicken Wrap (Easy Desi Style)', 'A Chicken Wrap is a quick meal made with spicy cooked chicken, veggies, and sauces rolled inside a soft roti or tortilla. It is tasty, filling, and high in protein, perfect for lunch or a quick snack.', 'recipe_69dfd3526753f4.82730898.jpeg', 20, 150.00, 2, 'Indian', 7, 'active', '2026-04-15 18:05:06', '2026-04-15 18:05:06'),
(5, '🧀 Healthy Paneer Bhurji (Easy & Protein-Rich)', 'Paneer Bhurji is a quick and healthy Indian dish made by cooking crumbled paneer with onions, tomatoes, and spices. It has a soft, slightly spicy, and flavorful taste. Rich in protein and easy to prepare, it is a perfect light meal for breakfast, lunch, or dinner.', 'recipe_69dfd9e82e3bf7.00748378.jpeg', 15, 120.00, 3, 'Indian', 7, 'active', '2026-04-15 18:14:56', '2026-04-15 18:33:12'),
(6, '🥥🍛 Thai Green Curry (Simple Version)', 'A quick homemade version of the creamy, mildly spicy Thai curry', 'recipe_69dfd83b58cae8.96648445.jpeg', 30, 200.00, 2, 'Thai', 8, 'active', '2026-04-15 18:26:03', '2026-04-15 18:26:03'),
(7, 'Arabian Chicken Mandhi', 'Arabian Chicken Mandi is a traditional dish from the Arabian Peninsula, especially popular in Yemen. It features tender, spiced chicken cooked over fragrant basmati rice infused with mild spices and a signature smoky flavor. The dish is known for its simple seasoning, slow cooking (dum style), and rich aroma rather than heavy masala, making it both flavorful and comforting.', 'recipe_69e3502949fe96.13512453.jpeg', 75, 250.00, 2, 'Arabic', 7, 'active', '2026-04-18 09:34:33', '2026-04-18 09:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `steps`
--

CREATE TABLE `steps` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `step_number` tinyint(3) UNSIGNED NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `steps`
--

INSERT INTO `steps` (`id`, `recipe_id`, `step_number`, `description`) VALUES
(8, 2, 1, '1.	Take warm rice and add a little salt.'),
(9, 2, 2, '2.	Place filling in the center.'),
(10, 2, 3, '3.	Shape into triangle or ball.'),
(11, 2, 4, '4.	Wrap with seaweed (optional).'),
(12, 2, 5, '5.	Serve fresh.'),
(13, 3, 1, 'Heat oil/ghee\r\nAdd jeera → let it crackle'),
(14, 3, 2, 'Add veggies\r\nToss them for 2–3 mins (no need to overthink)'),
(15, 3, 3, 'Add oats\r\nDry roast for 1 min with veggies (adds flavor)'),
(16, 3, 4, 'Add water + spices\r\nPour water → add haldi + salt'),
(17, 3, 5, 'Cook it\r\nStir for 3–5 mins till soft & creamy'),
(18, 4, 1, '🍗 Cook chicken\r\nHeat oil in pan\r\nAdd chicken + spices (salt, chilli, turmeric, garam masala)\r\nCook 8–10 mins until fully done'),
(19, 4, 2, '🥬 Add veggies\r\nToss onion + capsicum\r\nCook 2–3 mins (slightly crunchy = best)'),
(20, 4, 3, '🌯 Assemble wrap\r\nWarm roti\r\nSpread chutney/mayo/curd\r\nAdd chicken filling\r\nRoll it tight like a shawarma 🌯'),
(21, 4, 4, '🔥 Upgrade options\r\nAdd cheese 🧀 → extra creamy\r\nAdd egg 🥚 → more protein\r\nAdd spicy mayo → shawarma style'),
(31, 6, 1, 'Heat oil in a pan'),
(32, 6, 2, 'Add green curry paste → sauté 1 minute (smells amazing 😄)'),
(33, 6, 3, 'Pour coconut milk → mix well'),
(34, 6, 4, 'Add chicken/paneer/tofu → cook 5–7 mins'),
(35, 6, 5, 'Add vegetables → cook until slightly soft'),
(36, 6, 6, 'Add salt + soy sauce (optional)'),
(37, 6, 7, 'Add basil leaves at the end'),
(38, 6, 8, 'Serve with steamed rice 🍚 (best combo)\r\nOr noodles 🍜'),
(48, 5, 1, 'Heat oil in a pan'),
(49, 5, 2, 'Add onion → sauté 1–2 mins'),
(50, 5, 3, 'Add tomato + spices → cook till soft'),
(51, 5, 4, 'Add capsicum (optional)'),
(52, 5, 5, 'Add green peas (optional)'),
(53, 5, 6, 'Crumble paneer and add it in'),
(54, 5, 7, 'Mix and cook 3–5 mins'),
(55, 5, 8, 'Finish with lemon + coriander'),
(56, 5, 9, 'You can sever with roti 🫓\r\nOr as a protein bowl\r\nOr stuffed in a wrap 🌯'),
(57, 7, 1, 'Wash the chicken thoroughly and pat it dry.'),
(58, 7, 2, 'In a large bowl, mix yogurt, ginger-garlic paste, lemon juice, spices, salt, and oil.'),
(59, 7, 3, 'Add chicken to the marinade, coat well, and let it rest for at least 1 hour (overnight is better).'),
(60, 7, 4, 'Soak basmati rice in water for 20–30 minutes, then drain.'),
(61, 7, 5, 'Heat oil or ghee in a large pot. Add whole spices (bay leaf, cloves, cardamom, cinnamon, pepper, cumin).'),
(62, 7, 6, 'Add sliced onions and fry until golden brown.'),
(63, 7, 7, 'Add chopped tomatoes and green chilies. Cook until tomatoes soften.'),
(64, 7, 8, 'Pour in water or stock, add salt, and bring it to a boil.'),
(65, 7, 9, 'Add soaked rice, cook until 70–80% done (slightly undercooked).'),
(66, 7, 10, 'Place a rack or foil stand over the rice and arrange marinated chicken on top. Cover tightly.'),
(67, 7, 11, 'Cook on low flame (dum) for 30–40 minutes until chicken is fully cooked and rice is fluffy.'),
(68, 7, 12, 'For smoky flavor: heat charcoal until red hot, place it in a small bowl inside the pot, drizzle oil on it, cover immediately for 5 minutes. Then remove and gently mix before serving.'),
(69, 7, 13, 'Serve hot with -\r\nTomato chutney or salna\r\nOnion salad with lemon\r\nRaita');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','creator','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `otp_code`, `otp_expires`) VALUES
(4, 'nashreen', 'nashreen@gmail.com', '$2y$12$0rcAQNWTmKaLGt4GqJn4Y.7gmHRLcyoy9kJnwXoH8CX4/zkGFWeQ.', 'creator', '2026-04-15 07:53:38', NULL, NULL),
(5, 'yooozee', 'yooozee@gmail.com', '$2y$12$wPEiEEk7mBXmyUE7hZhlyOnirJfMqgfMiNE0Rb44CHyFKOGQ8c1lS', 'admin', '2026-04-15 17:21:11', NULL, NULL),
(7, 'sana', 'fathimasana05@gmail.com', '$2y$12$jSzilpe7jIJ3Cyyb5izs5Oh0ypTVrSbrg1vdyNUpphdC8ssm8.F9y', 'creator', '2026-04-15 17:40:37', NULL, NULL),
(8, 'shanza', 'shanza@gmail.com', '$2y$12$aLNXsI8B0GBU8sGyr.VnzuclP2mJLaIX8Kdfpuw3ZntTlg.o/OAoS', 'creator', '2026-04-15 18:17:30', NULL, NULL),
(9, 'Alan', 'Alan@gmail.com', '$2y$12$4UWGbwYa37Ok3KWMWNLmi.NLqfQsOSgSiLxrK0WVcPOmnRfcv05GC', 'user', '2026-04-18 09:16:44', NULL, NULL),
(10, 'neshry', 'neshreeen@gmail.com', '$2y$12$z7nxtBcSYoRzZiQMlGGJvOnXq1zl6tNUVmUkVprp./36xZ1X7cOde', 'user', '2026-04-21 10:16:41', NULL, NULL),
(11, 'misterTest', 'akhnasva12@gmail.com', '$2y$10$Gpw6ci0CJZhalVoktyPADOYTXb2/V094pkhHOV9wnMrXn4RHOtkDW', 'user', '2026-04-25 12:07:24', '235904', '2026-04-25 15:21:45'),
(12, 'Creator', 'akhnas@gmail.com', '$2y$12$3AsC8gO/DZer5x2pA85TkeDM1CNj5kHBzEucjsPZoO7SwDNwAoda2', 'creator', '2026-04-25 14:01:58', NULL, NULL),
(13, 'Super Admin', 'pantrychefweb@gmail.com', '$2y$12$nHkjzBsXZFMBcmUqrZRGmeEHWaPf0I7G6yX1Xslj4TXbS0hiRD5Ve', 'admin', '2026-04-25 14:16:54', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE `views` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `views`
--

INSERT INTO `views` (`id`, `recipe_id`, `user_id`, `ip_address`, `viewed_at`) VALUES
(2, 5, 5, '::1', '2026-04-15 18:29:39'),
(3, 4, 9, '::1', '2026-04-18 09:17:00'),
(4, 7, 7, '::1', '2026-04-18 10:04:37'),
(5, 7, 7, '::1', '2026-04-18 10:35:16'),
(6, 3, 7, '::1', '2026-04-18 10:36:18'),
(7, 7, 10, '::1', '2026-04-25 09:59:18'),
(9, 7, 11, '::1', '2026-04-25 12:07:44'),
(10, 7, 11, '::1', '2026-04-25 13:14:15'),
(11, 3, 13, '::1', '2026-04-26 10:27:00'),
(12, 3, 13, '::1', '2026-04-26 10:43:33'),
(13, 3, 13, '::1', '2026-04-26 10:43:33'),
(14, 4, 13, '::1', '2026-04-26 10:43:37'),
(15, 4, 13, '::1', '2026-04-26 10:47:18'),
(16, 3, 13, '::1', '2026-04-26 10:47:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `creators`
--
ALTER TABLE `creators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `idx_user_viewed` (`user_id`,`viewed_at`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipe` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_creator` (`created_by`),
  ADD KEY `idx_budget` (`budget`),
  ADD KEY `idx_cooking_time` (`cooking_time`),
  ADD KEY `idx_cuisine` (`cuisine`),
  ADD KEY `idx_status` (`status`);
ALTER TABLE `recipes` ADD FULLTEXT KEY `idx_search` (`title`,`description`);

--
-- Indexes for table `steps`
--
ALTER TABLE `steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipe` (`recipe_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_recipe` (`recipe_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `creators`
--
ALTER TABLE `creators`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `steps`
--
ALTER TABLE `steps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `views`
--
ALTER TABLE `views`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `creators`
--
ALTER TABLE `creators`
  ADD CONSTRAINT `creators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `steps`
--
ALTER TABLE `steps`
  ADD CONSTRAINT `steps_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `views`
--
ALTER TABLE `views`
  ADD CONSTRAINT `views_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
