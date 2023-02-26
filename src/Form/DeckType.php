<?php
//src/Form/Type/DeckType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Deck;

class DeckType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('deckListUrl', BaseType\TextType::class, [
				'label' => 'Tappedout URL',
				'required' => true,
				'row_attr' => [
					'class' => 'form-floating',
				]
			])
			->add('name', BaseType\TextType::class,[
				'label' => 'Deck Name',
				'required' => true,
				'row_attr' => [
					'class' => 'form-floating',
				]
			])
			->add('backUrl', BaseType\TextType::class,[
				'label' => 'Custom Cardback URL (Optional)',
				'required' => false,
				'empty_data' => null,
				'row_attr' => [
					'class' => 'form-floating',
				]
			])
			->add('imageSize', BaseType\ChoiceType::class, [
				'choices'  => [
					'Small' => Deck::CARD_SIZE_SMALL,
					'Medium' => Deck::CARD_SIZE_MEDIUM,
					'Large (Default)' => Deck::CARD_SIZE_LARGE,
				],
				'empty_data' => Deck::CARD_SIZE_LARGE,
				'row_attr' => [
					'class' => 'form-floating',
				]
			])
			->add('submit', BaseType\SubmitType::class,[
				'label' => 'Create Deck File'
			])
		;
	}
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => Deck::class
		]);
	}
	
}
