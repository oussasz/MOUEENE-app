-- Seed a rich service catalog (EN + FR + AR)
-- Safe to re-run: uses ON DUPLICATE KEY UPDATE on service_slug and (service_id, language_code)

-- Resolve category IDs by slug
SET @cat_cleaning := (SELECT category_id FROM service_categories WHERE category_slug = 'cleaning' LIMIT 1);
SET @cat_gardening := (SELECT category_id FROM service_categories WHERE category_slug = 'gardening' LIMIT 1);
SET @cat_childcare := (SELECT category_id FROM service_categories WHERE category_slug = 'childcare' LIMIT 1);
SET @cat_tutoring := (SELECT category_id FROM service_categories WHERE category_slug = 'tutoring' LIMIT 1);
SET @cat_petcare := (SELECT category_id FROM service_categories WHERE category_slug = 'pet-care' LIMIT 1);
SET @cat_elderly := (SELECT category_id FROM service_categories WHERE category_slug = 'elderly-care' LIMIT 1);
SET @cat_nursing := (SELECT category_id FROM service_categories WHERE category_slug = 'nursing' LIMIT 1);
SET @cat_repairs := (SELECT category_id FROM service_categories WHERE category_slug = 'home-repairs' LIMIT 1);
SET @cat_beauty := (SELECT category_id FROM service_categories WHERE category_slug = 'beauty-wellness' LIMIT 1);
SET @cat_moving := (SELECT category_id FROM service_categories WHERE category_slug = 'moving-delivery' LIMIT 1);

-- ---------------------------------------------------------------------------
-- SERVICES (English base)
-- ---------------------------------------------------------------------------
INSERT INTO services (
  category_id,
  service_name,
  service_slug,
  description,
  detailed_description,
  service_image,
  duration_minutes,
  base_price,
  price_type,
  is_popular,
  is_featured,
  is_active
) VALUES
-- Cleaning Services
(@cat_cleaning, 'Standard House Cleaning', 'standard-house-cleaning', 'Routine cleaning of living areas, bedrooms, kitchen, and bathrooms.', 'Dusting, vacuuming/mopping, surfaces wipe-down, kitchen & bathroom sanitization, trash removal. Supplies can be provided by customer or provider.', '/assets/images/services/cleaning1.jpg', 120, 150.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_cleaning, 'Deep Cleaning', 'deep-cleaning-complete', 'Thorough deep cleaning for hard-to-reach areas and buildup removal.', 'Includes standard cleaning plus deep scrub of bathroom/kitchen, baseboards, behind/under accessible furniture, and detailed surface work.', '/assets/images/services/cleaning1.jpg', 180, 250.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_cleaning, 'Move-In / Move-Out Cleaning', 'move-in-move-out-cleaning', 'Full cleaning before moving in or after moving out.', 'Best for empty homes: floors, cabinets (inside/out), appliances exterior, bathroom descaling, windows (inside), and final touch-ups.', '/assets/images/services/cleaning1.jpg', 240, 320.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_cleaning, 'Post-Construction Cleaning', 'post-construction-cleaning', 'Remove dust and debris after renovation or construction.', 'Fine-dust removal, surface wipe-down, vacuuming, floors washing, and careful cleanup of residue. Heavy waste removal excluded unless agreed.', '/assets/images/services/cleaning1.jpg', 240, 380.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_cleaning, 'Office Cleaning', 'office-cleaning-standard', 'Professional cleaning for offices and workspaces.', 'Workstations, meeting rooms, common areas, kitchens, restrooms; includes trash, floors, and disinfection of high-touch points.', '/assets/images/services/cleaning1.jpg', 120, 220.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_cleaning, 'Window Cleaning (Interior)', 'window-cleaning-interior', 'Streak-free cleaning of interior windows and frames.', 'Glass, frames, and sills. Ladder work limited to safe indoor access.', '/assets/images/services/cleaning1.jpg', 60, 90.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_cleaning, 'Carpet Cleaning', 'carpet-cleaning', 'Deep carpet cleaning for stains and odors.', 'Pre-treatment, shampoo/extraction depending on equipment, and drying guidance. Severe stains may require additional treatment.', '/assets/images/services/cleaning1.jpg', 90, 180.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_cleaning, 'Upholstery Cleaning', 'upholstery-cleaning', 'Cleaning for sofas, chairs, and fabric upholstery.', 'Spot treatment, gentle extraction, deodorizing options depending on fabric type.', '/assets/images/services/cleaning1.jpg', 90, 160.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_cleaning, 'Kitchen Detail Cleaning', 'kitchen-detail-cleaning', 'Focused deep cleaning for kitchen surfaces and grease areas.', 'Counters, cabinets exterior, backsplash, sink, stove exterior; optional inside oven/fridge add-ons.', '/assets/images/services/cleaning1.jpg', 90, 140.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_cleaning, 'Bathroom Sanitization', 'bathroom-sanitization', 'Deep sanitization of toilets, showers, and sinks.', 'Descaling, grout scrub, mirrors, fixtures polish, and disinfection of high-touch points.', '/assets/images/services/cleaning1.jpg', 60, 110.00, 'fixed', TRUE, FALSE, TRUE),

-- Gardening & Landscaping
(@cat_gardening, 'Lawn Mowing', 'lawn-mowing', 'Professional lawn mowing and edge trimming.', 'Mowing, edging, and light cleanup of clippings. Includes basic garden pathway tidying.', '/assets/images/services/gardening1.jpg', 60, 100.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_gardening, 'Garden Maintenance', 'garden-maintenance', 'Ongoing garden care: pruning, watering, weeding, and cleanup.', 'Seasonal care plan can be arranged. Includes basic pruning and debris removal.', '/assets/images/services/gardening1.jpg', 120, 180.00, 'hourly', TRUE, TRUE, TRUE),
(@cat_gardening, 'Hedge Trimming', 'hedge-trimming', 'Trim hedges for clean shape and healthy growth.', 'Height and shape trimming, cleanup, and disposal options.', '/assets/images/services/gardening1.jpg', 90, 150.00, 'hourly', FALSE, FALSE, TRUE),
(@cat_gardening, 'Tree Pruning (Small/Medium)', 'tree-pruning', 'Pruning to improve safety and tree health.', 'Limited to small/medium trees; heavy climbing or high-risk work requires specialized service.', '/assets/images/services/gardening1.jpg', 120, 220.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_gardening, 'Seasonal Garden Cleanup', 'seasonal-garden-cleanup', 'Spring/Fall cleanup: leaves, dead plants, and garden refresh.', 'Leaf removal, bed cleanup, light weeding, and garden waste bagging.', '/assets/images/services/gardening1.jpg', 120, 200.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_gardening, 'Weed Removal', 'weed-removal', 'Remove weeds from garden beds and pathways.', 'Manual removal and optional eco-friendly prevention guidance.', '/assets/images/services/gardening1.jpg', 90, 140.00, 'hourly', FALSE, FALSE, TRUE),

-- Childcare & Babysitting
(@cat_childcare, 'Babysitting (Hourly)', 'babysitting-hourly', 'Reliable babysitting for children of all ages.', 'Playtime, meals, supervision, bedtime routines. Parent instructions followed strictly.', '/assets/images/services/childcare1.jpg', 180, 80.00, 'hourly', TRUE, TRUE, TRUE),
(@cat_childcare, 'After-School Care', 'after-school-care', 'Pickup and care after school with homework support.', 'School pickup (if agreed), snack, homework supervision, and safe activities.', '/assets/images/services/childcare2.jpg', 180, 120.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_childcare, 'Newborn Care (Night)', 'newborn-care-night', 'Night support for newborn routines and parent rest.', 'Feeding support, soothing, diaper changes, and safe sleep practices. Non-medical care only.', '/assets/images/services/childcare3.jpg', 240, 140.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_childcare, 'Weekend Childcare', 'weekend-childcare', 'Weekend childcare for errands, events, or rest.', 'Flexible scheduling, indoor/outdoor activities, and meal assistance.', '/assets/images/services/childcare1.jpg', 240, 90.00, 'hourly', FALSE, FALSE, TRUE),
(@cat_childcare, 'Homework Help (Kids)', 'homework-help-kids', 'Support children with homework and study routines.', 'Structured study time, reading practice, and school-task supervision.', '/assets/images/services/childcare2.jpg', 90, 70.00, 'hourly', FALSE, FALSE, TRUE),
(@cat_childcare, 'Childcare for Events', 'childcare-for-events', 'On-site childcare during weddings or family events.', 'Group supervision, games, and safety monitoring; can be staffed by multiple caregivers.', '/assets/images/services/childcare3.jpg', 240, 200.00, 'fixed', TRUE, FALSE, TRUE),

-- Tutoring & Education
(@cat_tutoring, 'Math Tutoring', 'math-tutoring', 'Personalized math tutoring for school or exam preparation.', 'From basics to advanced topics; exercises and progress tracking included.', '/assets/images/services/tutoring1.jpg', 60, 100.00, 'hourly', TRUE, TRUE, TRUE),
(@cat_tutoring, 'Physics Tutoring', 'physics-tutoring', 'Concept-driven physics tutoring with problem solving.', 'Mechanics, electricity, optics; tailored to curriculum and exam goals.', '/assets/images/services/tutoring1.jpg', 60, 110.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_tutoring, 'Chemistry Tutoring', 'chemistry-tutoring', 'Chemistry tutoring with clear explanations and practice.', 'Stoichiometry, reactions, acids/bases, organic basics; includes exercises.', '/assets/images/services/tutoring1.jpg', 60, 110.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_tutoring, 'English Tutoring', 'english-tutoring', 'Improve English speaking, reading, and writing skills.', 'Conversation practice, grammar, vocabulary, and writing feedback.', '/assets/images/services/tutoring1.jpg', 60, 90.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_tutoring, 'French Tutoring', 'french-tutoring', 'French tutoring for school or everyday communication.', 'Grammar, speaking practice, reading and writing exercises.', '/assets/images/services/tutoring1.jpg', 60, 90.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_tutoring, 'Exam Preparation Coaching', 'exam-prep-coaching', 'Structured coaching for school and national exams.', 'Study plan, timed practice, and guidance on exam strategy.', '/assets/images/services/tutoring1.jpg', 90, 140.00, 'hourly', TRUE, FALSE, TRUE),

-- Pet Care
(@cat_petcare, 'Dog Walking', 'dog-walking', 'Safe dog walking with attention to your pet’s routine.', 'Leash walk, hydration check, and behavior notes after the walk.', '/assets/images/services/cleaning1.jpg', 30, 50.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_petcare, 'Pet Sitting (In-Home)', 'pet-sitting-in-home', 'In-home pet sitting visits for feeding and care.', 'Feeding, fresh water, litter/area cleanup, and playtime; visit reports included.', '/assets/images/services/cleaning1.jpg', 60, 80.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_petcare, 'Cat Care Visit', 'cat-care-visit', 'Daily cat visit: feeding, litter, and companionship.', 'Includes feeding, water refresh, litter cleanup, and play/comfort time.', '/assets/images/services/cleaning1.jpg', 45, 60.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_petcare, 'Basic Pet Grooming', 'basic-pet-grooming', 'Basic grooming: brushing and hygiene check.', 'Brushing, nail trim (if pet allows), and coat check; bathing optional.', '/assets/images/services/cleaning1.jpg', 60, 90.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_petcare, 'Puppy Care Visit', 'puppy-care-visit', 'Short visits for puppies: feeding and potty routine.', 'Feeding, potty break, supervised play, and basic routine reinforcement.', '/assets/images/services/cleaning1.jpg', 30, 55.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_petcare, 'Vet Transport Assistance', 'vet-transport-assistance', 'Help transporting pets to veterinary appointments.', 'Pickup, safe transport, and drop-off; waiting time can be included.', '/assets/images/services/cleaning1.jpg', 60, 120.00, 'fixed', FALSE, FALSE, TRUE),

-- Elderly Care
(@cat_elderly, 'Companion Care', 'companion-care', 'Friendly companionship and daily support for seniors.', 'Conversation, light activities, meal assistance, and safety monitoring.', '/assets/images/services/cleaning1.jpg', 180, 150.00, 'hourly', TRUE, TRUE, TRUE),
(@cat_elderly, 'Personal Care Assistance', 'personal-care-assistance', 'Help with hygiene, dressing, and mobility support.', 'Non-medical assistance with respect and privacy; follows family guidance.', '/assets/images/services/cleaning1.jpg', 120, 140.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_elderly, 'Medication Reminder Visits', 'medication-reminder-visits', 'Reminders and routine support for scheduled medication.', 'Non-medical reminders only; does not replace medical supervision.', '/assets/images/services/cleaning1.jpg', 45, 80.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_elderly, 'Meal Preparation for Seniors', 'meal-prep-for-seniors', 'Prepare healthy meals adapted to dietary needs.', 'Cooking and kitchen cleanup; grocery list planning if needed.', '/assets/images/services/cleaning1.jpg', 90, 120.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_elderly, 'Errands & Shopping Assistance', 'errands-shopping-assistance', 'Assistance with errands, shopping, and small tasks.', 'Accompaniment, carrying items, and safe return home.', '/assets/images/services/cleaning1.jpg', 90, 110.00, 'hourly', FALSE, FALSE, TRUE),
(@cat_elderly, 'Overnight Elderly Support', 'overnight-elderly-support', 'Overnight presence for safety and comfort.', 'Night monitoring, light assistance, and emergency readiness.', '/assets/images/services/cleaning1.jpg', 480, 280.00, 'fixed', TRUE, FALSE, TRUE),

-- Nursing & Medical
(@cat_nursing, 'Home Nursing Visit', 'home-nursing-visit', 'Professional nursing care at home (visit-based).', 'Assessment, care plan support, and nursing procedures as appropriate.', '/assets/images/services/cleaning1.jpg', 60, 180.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_nursing, 'Wound Dressing', 'wound-dressing', 'Wound care and dressing change at home.', 'Cleaning, dressing change, and monitoring for signs of infection.', '/assets/images/services/cleaning1.jpg', 45, 140.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_nursing, 'Injection Administration', 'injection-administration', 'Administer prescribed injections at home.', 'Requires valid prescription and supplies; documentation available.', '/assets/images/services/cleaning1.jpg', 30, 120.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_nursing, 'Vital Signs Monitoring', 'vital-signs-monitoring', 'Monitor and record vital signs during a home visit.', 'Blood pressure, pulse, temperature, and other basic measurements.', '/assets/images/services/cleaning1.jpg', 30, 90.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_nursing, 'Medication Management (Support)', 'medication-management-support', 'Support organizing medication schedules and adherence.', 'Non-prescribing assistance; coordination with family and care team.', '/assets/images/services/cleaning1.jpg', 60, 130.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_nursing, 'Post-Surgery Home Care', 'post-surgery-home-care', 'After-surgery support at home including monitoring and basic care.', 'Wound checks, mobility support, and follow-up reminders as appropriate.', '/assets/images/services/cleaning1.jpg', 120, 220.00, 'fixed', TRUE, FALSE, TRUE),

-- Home Repairs
(@cat_repairs, 'Plumbing Repair', 'plumbing-repair', 'Fix leaks, clogged drains, and common plumbing issues.', 'Diagnosis and repair for taps, sinks, toilets, and visible pipe issues (parts extra).', '/assets/images/services/cleaning1.jpg', 90, 180.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_repairs, 'Electrical Repair', 'electrical-repair', 'Troubleshoot and fix common electrical problems.', 'Switches, outlets, lighting fixtures; safety-first approach (materials extra).', '/assets/images/services/cleaning1.jpg', 90, 200.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_repairs, 'Air Conditioner Maintenance', 'ac-maintenance', 'AC inspection, cleaning, and basic maintenance.', 'Filter cleaning, airflow check, and basic diagnostics; gas refill not included unless agreed.', '/assets/images/services/cleaning1.jpg', 90, 220.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_repairs, 'Appliance Repair (Basic)', 'appliance-repair-basic', 'Basic diagnosis and repair for home appliances.', 'Washing machine, fridge, oven, and small appliances depending on issue.', '/assets/images/services/cleaning1.jpg', 90, 200.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_repairs, 'Furniture Assembly', 'furniture-assembly', 'Assemble furniture and install basic fixtures.', 'Beds, desks, shelves; includes tightening and stability check.', '/assets/images/services/cleaning1.jpg', 90, 150.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_repairs, 'Painting (One Room)', 'painting-one-room', 'Interior painting for a standard-sized room.', 'Surface prep, masking, painting, and cleanup. Paint provided by customer unless agreed.', '/assets/images/services/cleaning1.jpg', 240, 400.00, 'fixed', FALSE, FALSE, TRUE),

-- Beauty & Wellness
(@cat_beauty, 'Haircut at Home', 'haircut-at-home', 'Professional haircut service in your home.', 'Consultation, haircut, styling, and cleanup of hair clippings.', '/assets/images/services/cleaning1.jpg', 60, 90.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_beauty, 'Manicure & Pedicure', 'manicure-pedicure', 'Complete nail care service at home.', 'Nail shaping, cuticle care, and polish application (optional).', '/assets/images/services/cleaning1.jpg', 90, 140.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_beauty, 'Makeup Artist', 'makeup-artist', 'Makeup service for events and special occasions.', 'Look consultation, application, and touch-up tips.', '/assets/images/services/cleaning1.jpg', 90, 180.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_beauty, 'Massage Session', 'massage-session', 'Relaxing massage session at home.', 'Pressure and focus adjusted to preference; non-medical wellness service.', '/assets/images/services/cleaning1.jpg', 60, 200.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_beauty, 'Waxing Service', 'waxing-service', 'At-home waxing service with hygiene standards.', 'Area selection and aftercare guidance included.', '/assets/images/services/cleaning1.jpg', 60, 160.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_beauty, 'Fitness Training Session', 'fitness-training-session', 'Personal training session adapted to your goals.', 'Warm-up, training plan, and cooldown; guidance for safe home workouts.', '/assets/images/services/cleaning1.jpg', 60, 180.00, 'hourly', TRUE, FALSE, TRUE),

-- Moving & Delivery
(@cat_moving, 'Moving Assistance (2 Helpers)', 'moving-assistance-2-helpers', 'Help loading/unloading and moving heavy items.', 'Includes lifting, carrying, and basic organization. Vehicle not included unless agreed.', '/assets/images/services/cleaning1.jpg', 180, 300.00, 'fixed', TRUE, TRUE, TRUE),
(@cat_moving, 'Packing & Unpacking', 'packing-unpacking', 'Packing or unpacking help with organization.', 'Boxes, wrapping, labeling, and room-by-room organization guidance.', '/assets/images/services/cleaning1.jpg', 180, 220.00, 'hourly', TRUE, FALSE, TRUE),
(@cat_moving, 'Furniture Disassembly/Reassembly', 'furniture-disassembly-reassembly', 'Disassemble and reassemble furniture for moving.', 'Beds, wardrobes, desks; hardware kept organized and reassembled safely.', '/assets/images/services/cleaning1.jpg', 120, 200.00, 'fixed', FALSE, FALSE, TRUE),
(@cat_moving, 'Local Delivery (Small Items)', 'local-delivery-small-items', 'Same-day delivery for small items within the city.', 'Pickup and drop-off with tracking updates; distance limits may apply.', '/assets/images/services/cleaning1.jpg', 60, 120.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_moving, 'Grocery & Errand Delivery', 'grocery-errand-delivery', 'Grocery pickup and delivery to your door.', 'Shopping list support, substitutions confirmation, and safe delivery.', '/assets/images/services/cleaning1.jpg', 60, 90.00, 'fixed', TRUE, FALSE, TRUE),
(@cat_moving, 'Junk Removal (Light)', 'junk-removal-light', 'Removal of light junk and disposal assistance.', 'Bagged trash, small furniture; heavy/industrial waste excluded unless agreed.', '/assets/images/services/cleaning1.jpg', 120, 180.00, 'fixed', FALSE, FALSE, TRUE)
ON DUPLICATE KEY UPDATE
  category_id = VALUES(category_id),
  service_name = VALUES(service_name),
  description = VALUES(description),
  detailed_description = VALUES(detailed_description),
  service_image = VALUES(service_image),
  duration_minutes = VALUES(duration_minutes),
  base_price = VALUES(base_price),
  price_type = VALUES(price_type),
  is_popular = VALUES(is_popular),
  is_featured = VALUES(is_featured),
  is_active = VALUES(is_active);

-- ---------------------------------------------------------------------------
-- TRANSLATIONS (French + Arabic)
-- ---------------------------------------------------------------------------

-- French
INSERT INTO service_translations (service_id, language_code, translated_name, translated_description, translated_detailed_description)
SELECT s.service_id, 'fr', t.translated_name, t.translated_description, NULL
FROM (
  SELECT 'standard-house-cleaning' AS service_slug, 'Nettoyage standard de maison' AS translated_name, 'Nettoyage régulier des pièces, cuisine et salles de bain.' AS translated_description
  UNION ALL SELECT 'deep-cleaning-complete', 'Nettoyage en profondeur', 'Nettoyage complet pour éliminer les dépôts et atteindre les zones difficiles.'
  UNION ALL SELECT 'move-in-move-out-cleaning', 'Nettoyage entrée/sortie', 'Nettoyage complet avant emménagement ou après déménagement.'
  UNION ALL SELECT 'post-construction-cleaning', 'Nettoyage après travaux', 'Élimination de la poussière et des résidus après rénovation.'
  UNION ALL SELECT 'office-cleaning-standard', 'Nettoyage de bureau', 'Nettoyage professionnel des espaces de travail.'
  UNION ALL SELECT 'window-cleaning-interior', 'Nettoyage des vitres (intérieur)', 'Nettoyage sans traces des vitres, cadres et rebords.'
  UNION ALL SELECT 'carpet-cleaning', 'Nettoyage de tapis', 'Nettoyage en profondeur des tapis pour taches et odeurs.'
  UNION ALL SELECT 'upholstery-cleaning', 'Nettoyage de canapés/tissus', 'Nettoyage des canapés et tissus d’ameublement.'
  UNION ALL SELECT 'kitchen-detail-cleaning', 'Nettoyage détaillé de cuisine', 'Dégraissage et nettoyage approfondi des surfaces de cuisine.'
  UNION ALL SELECT 'bathroom-sanitization', 'Désinfection de salle de bain', 'Désinfection et détartrage des sanitaires.'

  UNION ALL SELECT 'lawn-mowing', 'Tonte de pelouse', 'Tonte professionnelle et bordures.'
  UNION ALL SELECT 'garden-maintenance', 'Entretien de jardin', 'Entretien régulier : taille, arrosage, désherbage et nettoyage.'
  UNION ALL SELECT 'hedge-trimming', 'Taille de haies', 'Taille et mise en forme des haies avec nettoyage.'
  UNION ALL SELECT 'tree-pruning', 'Élagage (petit/moyen)', 'Élagage pour la sécurité et la santé de l’arbre.'
  UNION ALL SELECT 'seasonal-garden-cleanup', 'Nettoyage saisonnier du jardin', 'Nettoyage de printemps/automne : feuilles et déchets verts.'
  UNION ALL SELECT 'weed-removal', 'Désherbage', 'Retrait des mauvaises herbes des massifs et allées.'

  UNION ALL SELECT 'babysitting-hourly', 'Baby-sitting (horaire)', 'Garde d’enfants fiable et attentive.'
  UNION ALL SELECT 'after-school-care', 'Garde après l’école', 'Prise en charge après l’école avec aide aux devoirs.'
  UNION ALL SELECT 'newborn-care-night', 'Garde nouveau-né (nuit)', 'Soutien de nuit pour la routine du nouveau-né.'
  UNION ALL SELECT 'weekend-childcare', 'Garde le week-end', 'Garde d’enfants le week-end pour événements ou repos.'
  UNION ALL SELECT 'homework-help-kids', 'Aide aux devoirs (enfants)', 'Accompagnement des devoirs et des révisions.'
  UNION ALL SELECT 'childcare-for-events', 'Garde d’enfants pour événements', 'Garde sur place lors de mariages ou fêtes.'

  UNION ALL SELECT 'math-tutoring', 'Cours de mathématiques', 'Soutien personnalisé en mathématiques.'
  UNION ALL SELECT 'physics-tutoring', 'Cours de physique', 'Soutien en physique avec exercices et méthodes.'
  UNION ALL SELECT 'chemistry-tutoring', 'Cours de chimie', 'Soutien en chimie avec explications et pratique.'
  UNION ALL SELECT 'english-tutoring', 'Cours d’anglais', 'Améliorez l’anglais : oral, écrit, lecture.'
  UNION ALL SELECT 'french-tutoring', 'Cours de français', 'Soutien en français pour l’école ou le quotidien.'
  UNION ALL SELECT 'exam-prep-coaching', 'Préparation aux examens', 'Coaching structuré et stratégie d’examen.'

  UNION ALL SELECT 'dog-walking', 'Promenade de chien', 'Promenade sécurisée selon la routine de votre chien.'
  UNION ALL SELECT 'pet-sitting-in-home', 'Garde d’animaux (à domicile)', 'Visites à domicile : nourriture, eau et compagnie.'
  UNION ALL SELECT 'cat-care-visit', 'Visite pour chat', 'Nourriture, litière et moment de compagnie.'
  UNION ALL SELECT 'basic-pet-grooming', 'Toilettage de base', 'Brossage et soins de base (selon l’animal).' 
  UNION ALL SELECT 'puppy-care-visit', 'Visite chiot', 'Visites courtes : routine, nourriture et jeux.'
  UNION ALL SELECT 'vet-transport-assistance', 'Transport vétérinaire', 'Aide au transport vers les rendez-vous vétérinaires.'

  UNION ALL SELECT 'companion-care', 'Compagnie pour seniors', 'Compagnie et soutien quotidien pour personnes âgées.'
  UNION ALL SELECT 'personal-care-assistance', 'Aide à la toilette et mobilité', 'Assistance à l’hygiène, l’habillage et la mobilité.'
  UNION ALL SELECT 'medication-reminder-visits', 'Rappels de médicaments', 'Rappels de prise de médicaments (non médical).' 
  UNION ALL SELECT 'meal-prep-for-seniors', 'Préparation de repas (seniors)', 'Préparation de repas adaptés aux besoins.'
  UNION ALL SELECT 'errands-shopping-assistance', 'Courses et accompagnement', 'Aide pour courses et petites tâches.'
  UNION ALL SELECT 'overnight-elderly-support', 'Présence de nuit (seniors)', 'Présence nocturne pour sécurité et confort.'

  UNION ALL SELECT 'home-nursing-visit', 'Visite infirmière à domicile', 'Soins infirmiers professionnels à domicile.'
  UNION ALL SELECT 'wound-dressing', 'Pansement', 'Soins de plaie et changement de pansement.'
  UNION ALL SELECT 'injection-administration', 'Injection à domicile', 'Administration d’injections prescrites.'
  UNION ALL SELECT 'vital-signs-monitoring', 'Contrôle des constantes', 'Mesure et suivi des signes vitaux.'
  UNION ALL SELECT 'medication-management-support', 'Gestion des médicaments (soutien)', 'Organisation et suivi de la prise de médicaments.'
  UNION ALL SELECT 'post-surgery-home-care', 'Soins post-opératoires', 'Soutien à domicile après chirurgie.'

  UNION ALL SELECT 'plumbing-repair', 'Réparation plomberie', 'Fuites, débouchage et réparations courantes.'
  UNION ALL SELECT 'electrical-repair', 'Réparation électrique', 'Dépannage prises, interrupteurs et éclairage.'
  UNION ALL SELECT 'ac-maintenance', 'Entretien climatisation', 'Inspection et entretien de climatiseur.'
  UNION ALL SELECT 'appliance-repair-basic', 'Réparation d’électroménager', 'Diagnostic et réparation de base des appareils.'
  UNION ALL SELECT 'furniture-assembly', 'Montage de meubles', 'Montage de meubles et vérification de stabilité.'
  UNION ALL SELECT 'painting-one-room', 'Peinture (une pièce)', 'Peinture intérieure d’une pièce avec préparation.'

  UNION ALL SELECT 'haircut-at-home', 'Coupe à domicile', 'Coupe professionnelle à domicile.'
  UNION ALL SELECT 'manicure-pedicure', 'Manucure & pédicure', 'Soin complet des ongles à domicile.'
  UNION ALL SELECT 'makeup-artist', 'Maquillage', 'Maquillage pour événements.'
  UNION ALL SELECT 'massage-session', 'Massage', 'Massage relaxant à domicile.'
  UNION ALL SELECT 'waxing-service', 'Épilation', 'Épilation à domicile avec hygiène.'
  UNION ALL SELECT 'fitness-training-session', 'Coaching sportif', 'Séance de sport adaptée à vos objectifs.'

  UNION ALL SELECT 'moving-assistance-2-helpers', 'Aide au déménagement (2 personnes)', 'Aide au chargement/déchargement et manutention.'
  UNION ALL SELECT 'packing-unpacking', 'Emballage/Déballage', 'Aide au rangement, emballage et organisation.'
  UNION ALL SELECT 'furniture-disassembly-reassembly', 'Démontage/Remontage de meubles', 'Démonter et remonter les meubles pour déménagement.'
  UNION ALL SELECT 'local-delivery-small-items', 'Livraison locale (petits objets)', 'Livraison le jour même en ville (petits objets).' 
  UNION ALL SELECT 'grocery-errand-delivery', 'Livraison courses', 'Achat et livraison de courses à domicile.'
  UNION ALL SELECT 'junk-removal-light', 'Débarras léger', 'Enlèvement de petits encombrants (léger).' 
) t
JOIN services s ON s.service_slug = t.service_slug
ON DUPLICATE KEY UPDATE
  translated_name = VALUES(translated_name),
  translated_description = VALUES(translated_description),
  translated_detailed_description = VALUES(translated_detailed_description);

-- Arabic
INSERT INTO service_translations (service_id, language_code, translated_name, translated_description, translated_detailed_description)
SELECT s.service_id, 'ar', t.translated_name, t.translated_description, NULL
FROM (
  SELECT 'standard-house-cleaning' AS service_slug, 'تنظيف منزل عادي' AS translated_name, 'تنظيف دوري للغرف والمطبخ والحمّامات.' AS translated_description
  UNION ALL SELECT 'deep-cleaning-complete', 'تنظيف عميق', 'تنظيف شامل لإزالة الأوساخ المتراكمة والوصول للأماكن الصعبة.'
  UNION ALL SELECT 'move-in-move-out-cleaning', 'تنظيف قبل/بعد الانتقال', 'تنظيف كامل قبل السكن أو بعد المغادرة.'
  UNION ALL SELECT 'post-construction-cleaning', 'تنظيف بعد الأشغال', 'إزالة الغبار وبقايا الأشغال بعد الترميم.'
  UNION ALL SELECT 'office-cleaning-standard', 'تنظيف مكاتب', 'تنظيف احترافي للمكاتب ومساحات العمل.'
  UNION ALL SELECT 'window-cleaning-interior', 'تنظيف النوافذ (داخلي)', 'تنظيف الزجاج والإطارات من الداخل بدون آثار.'
  UNION ALL SELECT 'carpet-cleaning', 'تنظيف السجاد', 'تنظيف عميق للسجاد لإزالة البقع والروائح.'
  UNION ALL SELECT 'upholstery-cleaning', 'تنظيف الأرائك والمفروشات', 'تنظيف الكنب والمفروشات القماشية.'
  UNION ALL SELECT 'kitchen-detail-cleaning', 'تنظيف مطبخ تفصيلي', 'تنظيف عميق لأسطح المطبخ وإزالة الدهون.'
  UNION ALL SELECT 'bathroom-sanitization', 'تعقيم الحمّام', 'تعقيم وتنظيف شامل للحمّام وإزالة التكلسات.'

  UNION ALL SELECT 'lawn-mowing', 'جزّ العشب', 'جزّ العشب وتشذيب الحواف بشكل احترافي.'
  UNION ALL SELECT 'garden-maintenance', 'صيانة الحديقة', 'صيانة دورية: تقليم وسقي وإزالة الأعشاب وتنظيف.'
  UNION ALL SELECT 'hedge-trimming', 'تشذيب السياج النباتي', 'تشذيب وتشكيل السياج مع تنظيف المكان.'
  UNION ALL SELECT 'tree-pruning', 'تقليم الأشجار', 'تقليم لتحسين السلامة وصحة الشجرة.'
  UNION ALL SELECT 'seasonal-garden-cleanup', 'تنظيف موسمي للحديقة', 'تنظيف الربيع/الخريف وإزالة الأوراق والمخلفات.'
  UNION ALL SELECT 'weed-removal', 'إزالة الأعشاب الضارة', 'إزالة الأعشاب من أحواض الزراعة والممرات.'

  UNION ALL SELECT 'babysitting-hourly', 'جليسة أطفال (بالساعة)', 'رعاية موثوقة للأطفال لجميع الأعمار.'
  UNION ALL SELECT 'after-school-care', 'رعاية بعد المدرسة', 'استقبال بعد المدرسة مع مساعدة في الواجبات.'
  UNION ALL SELECT 'newborn-care-night', 'رعاية مولود (ليلاً)', 'مساندة ليلية لروتين المولود وراحة الأهل.'
  UNION ALL SELECT 'weekend-childcare', 'رعاية الأطفال في عطلة الأسبوع', 'رعاية مرنة لعطلة الأسبوع للراحة أو المناسبات.'
  UNION ALL SELECT 'homework-help-kids', 'مساعدة في الواجبات (أطفال)', 'تنظيم وقت الدراسة ومتابعة الواجبات.'
  UNION ALL SELECT 'childcare-for-events', 'رعاية أطفال للمناسبات', 'رعاية في مكان المناسبة أثناء الأعراس أو الحفلات.'

  UNION ALL SELECT 'math-tutoring', 'دروس رياضيات', 'دروس خصوصية في الرياضيات حسب المستوى.'
  UNION ALL SELECT 'physics-tutoring', 'دروس فيزياء', 'شرح مبسّط مع تمارين وحلول.'
  UNION ALL SELECT 'chemistry-tutoring', 'دروس كيمياء', 'دروس كيمياء مع تطبيقات وتمارين.'
  UNION ALL SELECT 'english-tutoring', 'دروس إنجليزية', 'تحسين المحادثة والقراءة والكتابة.'
  UNION ALL SELECT 'french-tutoring', 'دروس فرنسية', 'دروس فرنسية للمدرسة أو الاستخدام اليومي.'
  UNION ALL SELECT 'exam-prep-coaching', 'تحضير للامتحانات', 'خطة دراسة وتمارين موقّتة واستراتيجية للامتحان.'

  UNION ALL SELECT 'dog-walking', 'تمشية الكلاب', 'تمشية آمنة وفق روتين الحيوان.'
  UNION ALL SELECT 'pet-sitting-in-home', 'رعاية الحيوانات (زيارة منزلية)', 'زيارات منزلية للتغذية والماء والاهتمام.'
  UNION ALL SELECT 'cat-care-visit', 'زيارة رعاية القطط', 'تغذية وتنظيف صندوق الرمل ووقت للّعب.'
  UNION ALL SELECT 'basic-pet-grooming', 'تنظيف/تمشيط أساسي', 'تمشيط وفحص نظافة أساسي حسب الحيوان.'
  UNION ALL SELECT 'puppy-care-visit', 'زيارة رعاية جرو', 'زيارات قصيرة: تغذية وروتين ولعب.'
  UNION ALL SELECT 'vet-transport-assistance', 'مساعدة نقل للطبيب البيطري', 'نقل آمن لمواعيد الطبيب البيطري.'

  UNION ALL SELECT 'companion-care', 'مرافقة كبار السن', 'مرافقة ودعم يومي لكبار السن.'
  UNION ALL SELECT 'personal-care-assistance', 'مساعدة العناية الشخصية', 'مساعدة في النظافة واللباس والحركة.'
  UNION ALL SELECT 'medication-reminder-visits', 'تذكير بالأدوية', 'تذكير بمواعيد الأدوية (غير طبي).' 
  UNION ALL SELECT 'meal-prep-for-seniors', 'تحضير وجبات لكبار السن', 'تحضير وجبات صحية حسب الاحتياج.'
  UNION ALL SELECT 'errands-shopping-assistance', 'مساعدة في المشاوير والتسوق', 'مرافقة للتسوق وإنجاز المهام.'
  UNION ALL SELECT 'overnight-elderly-support', 'مرافقة ليلية لكبار السن', 'وجود ليلي للسلامة والاطمئنان.'

  UNION ALL SELECT 'home-nursing-visit', 'زيارة تمريض منزلية', 'رعاية تمريضية احترافية في المنزل.'
  UNION ALL SELECT 'wound-dressing', 'تضميد جروح', 'عناية بالجروح وتغيير الضماد.'
  UNION ALL SELECT 'injection-administration', 'حقن منزلية', 'إعطاء حقن بوصفة طبية في المنزل.'
  UNION ALL SELECT 'vital-signs-monitoring', 'قياس العلامات الحيوية', 'قياس وتسجيل الضغط والنبض والحرارة.'
  UNION ALL SELECT 'medication-management-support', 'تنظيم الأدوية (مساندة)', 'مساعدة في تنظيم جدول الأدوية والمتابعة.'
  UNION ALL SELECT 'post-surgery-home-care', 'رعاية ما بعد العملية', 'مساندة منزلية بعد الجراحة ومراقبة أساسية.'

  UNION ALL SELECT 'plumbing-repair', 'إصلاح السباكة', 'إصلاح تسربات وانسداد ومشاكل السباكة الشائعة.'
  UNION ALL SELECT 'electrical-repair', 'إصلاح الكهرباء', 'تشخيص وإصلاح مقابس ومفاتيح وإنارة.'
  UNION ALL SELECT 'ac-maintenance', 'صيانة المكيّف', 'فحص وتنظيف وصيانة أساسية للمكيّف.'
  UNION ALL SELECT 'appliance-repair-basic', 'إصلاح أجهزة منزلية', 'تشخيص وإصلاح أساسي للأجهزة المنزلية.'
  UNION ALL SELECT 'furniture-assembly', 'تركيب الأثاث', 'تركيب أثاث مع التأكد من الثبات.'
  UNION ALL SELECT 'painting-one-room', 'دهان غرفة واحدة', 'دهان داخلي لغرفة مع تجهيز السطح.'

  UNION ALL SELECT 'haircut-at-home', 'قص شعر منزلي', 'قص شعر احترافي في المنزل.'
  UNION ALL SELECT 'manicure-pedicure', 'مانيكير وبيديكير', 'عناية كاملة بالأظافر في المنزل.'
  UNION ALL SELECT 'makeup-artist', 'مكياج', 'مكياج للمناسبات والأفراح.'
  UNION ALL SELECT 'massage-session', 'جلسة مساج', 'مساج استرخائي في المنزل.'
  UNION ALL SELECT 'waxing-service', 'إزالة شعر بالشمع', 'إزالة شعر في المنزل مع احترام النظافة.'
  UNION ALL SELECT 'fitness-training-session', 'جلسة تدريب رياضي', 'تدريب شخصي حسب الأهداف والقدرة.'

  UNION ALL SELECT 'moving-assistance-2-helpers', 'مساعدة نقل (شخصان)', 'تحميل/تفريغ وحمل الأغراض الثقيلة.'
  UNION ALL SELECT 'packing-unpacking', 'تغليف/تفريغ', 'مساعدة في التغليف والترتيب والتنظيم.'
  UNION ALL SELECT 'furniture-disassembly-reassembly', 'تفكيك/تركيب أثاث', 'تفكيك الأثاث وإعادة تركيبه للنقل.'
  UNION ALL SELECT 'local-delivery-small-items', 'توصيل محلي (أغراض صغيرة)', 'توصيل داخل المدينة للأغراض الصغيرة.'
  UNION ALL SELECT 'grocery-errand-delivery', 'توصيل مشتريات ومشاوير', 'شراء وتوصيل المشتريات إلى باب المنزل.'
  UNION ALL SELECT 'junk-removal-light', 'رفع مخلفات خفيفة', 'إزالة مخلفات خفيفة وأغراض صغيرة.'
) t
JOIN services s ON s.service_slug = t.service_slug
ON DUPLICATE KEY UPDATE
  translated_name = VALUES(translated_name),
  translated_description = VALUES(translated_description),
  translated_detailed_description = VALUES(translated_detailed_description);
